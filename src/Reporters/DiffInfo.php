<?php

namespace ApprovalTests\Reporters;

class DiffInfo
{
    public $diffProgram;
    public $parameters;
    public $fileExtensions;

    public function __construct($diffProgram, $fileExtensions, $parameters = null)
    {
        $this->diffProgram = self::resolveWindowsPath($diffProgram);
        $this->parameters = $parameters != null ? $parameters : GenericDiffReporter::$STANDARD_ARGUMENTS;
        $this->fileExtensions = $fileExtensions;
    }

    private static function resolveWindowsPath( $diffProgram)
    {
        $tag = "{ProgramFiles}";
        $diffProgramS = new \Delight\Str\Str($diffProgram);
        if ($diffProgramS->startsWith($tag)) {
            $diffProgram = self::getPathInProgramFilesX86(substr($diffProgram, strlen($tag)));
        }
        return $diffProgram;
    }

    private static function getPathInProgramFilesX86( $path)
    {
        $paths = self::getProgramFilesPaths();
        return self::getFirstWorking($path, $paths, "C:\\Program Files\\");
    }

    public static function getFirstWorking( $path,  $paths,  $ifNotFoundDefault)
    {
        $fullPath = $ifNotFoundDefault . $path;
        foreach ($paths as $p) {
            $fullPath = $p . DIRECTORY_SEPARATOR . $path;
            if (file_exists($fullPath)) {
                break;
            }
        }
        return $fullPath;
    }

    public static function getProgramFilesPaths()
    {
        $paths = [
            getenv("ProgramFiles(x86)"),
            getenv("ProgramFiles"),
            getenv("ProgramW6432"),
            // Programs like Beyond Compare 4 that support per-user installation
            // will likely install themselves here.
            getenv("LocalAppData"),
        ];
        return array_unique(array_filter($paths));
    }
}