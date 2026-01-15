<?php

namespace Domain\Letter\Actions;

use Domain\Letter\Services\GeminiLetterAnalysisService;
use Illuminate\Http\UploadedFile;
use Infra\Shared\Foundations\Action;

class AnalyzeLetterAction extends Action
{
    protected $geminiService;

    public function __construct(GeminiLetterAnalysisService $geminiService)
    {
        $this->geminiService = $geminiService;
    }

    public function execute(UploadedFile $file, string $letterType)
    {
        $mimeType = $file->getMimeType();
        $content = $file->get();

        return $this->geminiService->analyze($content, $mimeType, $letterType);
    }
}
