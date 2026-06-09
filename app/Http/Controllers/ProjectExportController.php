<?php

namespace App\Http\Controllers;

use App\Exports\ProjectTasksExport;
use App\Models\Project;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Facades\Excel;
use PhpOffice\PhpWord\IOFactory;
use PhpOffice\PhpWord\PhpWord;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ProjectExportController extends Controller
{
    public function __invoke(Request $request, Project $project): mixed
    {
        $request->validate([
            'format'       => 'required|in:excel,word',
            'types'        => 'nullable|array',
            'types.*'      => 'in:bug,feature,improvement,chore,question',
            'priorities'   => 'nullable|array',
            'priorities.*' => 'in:critical,high,medium,low',
            'statuses'     => 'nullable|array',
            'statuses.*'   => 'integer|exists:project_statuses,id',
        ]);

        $types      = $request->input('types', []);
        $priorities = $request->input('priorities', []);
        $statuses   = $request->input('statuses', []);
        $format     = $request->input('format');

        if ($format === 'excel') {
            return $this->exportExcel($project, $types, $priorities, $statuses);
        }

        return $this->exportWord($project, $types, $priorities, $statuses);
    }

    private function exportExcel(Project $project, array $types, array $priorities, array $statuses)
    {
        $filename = Str::slug($project->key) . '-tasks-' . now()->format('Ymd') . '.xlsx';
        return Excel::download(new ProjectTasksExport($project, $types, $priorities, $statuses), $filename);
    }

    private function exportWord(Project $project, array $types, array $priorities, array $statuses): StreamedResponse
    {
        $tasks = $project->tasks()
            ->with(['status', 'categories', 'assignee:id,name', 'attachments'])
            ->when($types, fn($q) => $q->whereIn('type', $types))
            ->when($priorities, fn($q) => $q->whereIn('priority', $priorities))
            ->when($statuses, fn($q) => $q->whereIn('status_id', $statuses))
            ->orderBy('position')
            ->get();

        $phpWord = new PhpWord();
        $phpWord->setDefaultFontName('Calibri');
        $phpWord->setDefaultFontSize(11);

        $phpWord->addTitleStyle(null, ['bold' => true, 'size' => 20]);
        $phpWord->addTitleStyle(1,    ['bold' => true, 'size' => 14]);

        $section = $phpWord->addSection();

        $section->addTitle($project->name, 0);
        $section->addTextBreak(1);

        $lastIndex = $tasks->count() - 1;

        foreach ($tasks as $index => $task) {
            $section->addTitle($task->task_number . ' — ' . $task->title, 1);

            $metaParts = array_filter([
                'Type: ' . ucfirst($task->type),
                'Priority: ' . ucfirst($task->priority),
                'Status: ' . $task->status->name,
                $task->assignee ? 'Assignee: ' . $task->assignee->name : null,
                $task->due_date ? 'Due: ' . $task->due_date->format('Y-m-d') : null,
            ]);
            $section->addText(implode('  |  ', $metaParts), ['italic' => true, 'color' => '666666']);
            $section->addTextBreak(1);

            if ($task->description) {
                foreach (explode("\n", $task->description) as $line) {
                    $line = trim($line);
                    if ($line !== '') {
                        $section->addText(htmlspecialchars($line, ENT_XML1, 'UTF-8'));
                    } else {
                        $section->addTextBreak(1);
                    }
                }
            } else {
                $section->addText('(No description)', ['color' => '999999']);
            }

            $section->addTextBreak(1);

            $this->appendAttachments($section, $task->attachments->all());

            if ($index < $lastIndex) {
                $section->addPageBreak();
            }
        }

        $filename = Str::slug($project->key) . '-tasks-' . now()->format('Ymd') . '.docx';

        return response()->stream(function () use ($phpWord) {
            $writer = IOFactory::createWriter($phpWord, 'Word2007');
            $writer->save('php://output');
        }, 200, [
            'Content-Type'        => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
            'Cache-Control'       => 'no-store, no-cache',
            'Pragma'              => 'no-cache',
        ]);
    }

    private function appendAttachments(\PhpOffice\PhpWord\Element\Section $section, array $attachments): void
    {
        if (empty($attachments)) {
            return;
        }

        $section->addText('Attachments:', ['bold' => true]);

        foreach ($attachments as $att) {
            $physicalPath = storage_path('app/private/' . $att->path);

            if (! file_exists($physicalPath)) {
                $section->addText('  [Missing file: ' . $att->filename . ']', ['color' => 'AA0000']);
                continue;
            }

            $mime = $att->mime_type ?? '';
            $ext  = strtolower(pathinfo($att->filename, PATHINFO_EXTENSION));

            if (in_array($mime, ['image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'image/webp'])) {
                try {
                    $section->addImage($physicalPath, [
                        'width'         => 400,
                        'ratio'         => true,
                        'wrappingStyle' => 'inline',
                    ]);
                    $section->addText($att->filename, ['size' => 8, 'color' => '888888']);
                } catch (\Throwable) {
                    $section->addText('  [Image could not be embedded: ' . $att->filename . ']', ['color' => 'AA0000']);
                }
                continue;
            }

            if (in_array($ext, ['doc', 'docx', 'xls', 'xlsx', 'ppt', 'pptx'])) {
                try {
                    $section->addOleObject($physicalPath);
                    $section->addText($att->filename, ['size' => 8, 'color' => '888888']);
                } catch (\Throwable) {
                    $section->addText('  [Attachment: ' . $att->filename . ' — could not embed]', ['color' => '888888']);
                }
                continue;
            }

            $section->addText(
                '  [Attachment: ' . $att->filename . ' (' . $att->formattedSize() . ')]',
                ['color' => '888888']
            );
        }

        $section->addTextBreak(1);
    }
}
