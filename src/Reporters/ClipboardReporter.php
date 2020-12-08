<?php

namespace ApprovalTests\Reporters;

use ApprovalTests\SystemUtil;

class ClipboardReporter implements Reporter
{
    public static function getCommandLineFor( $approvedFilename,  $receivedFilename,  $isWindows)
    {
        if ($isWindows) {
            return "move /Y \"$receivedFilename\" \"$approvedFilename\"";
        } else {
            return "mv \"$receivedFilename\" \"$approvedFilename\"";
        }
    }

    public function report($approvedFilename, $receivedFilename)
    {
        $commandLine = self::getCommandLineFor($approvedFilename, $receivedFilename, SystemUtil::isWindows());
        self::copyToClipboard($commandLine);
    }

    public static function copyToClipboard( $newClipboardText)
    {
        $clipboardCommand = SystemUtil::isWindows() ? "clip" : "pbclip";
        $cmd = "echo " . $newClipboardText . " | " . $clipboardCommand;
		shell_exec($cmd);
    }

    public function isWorkingInThisEnvironment($receivedFilename)
    {
        return true;
    }
}