<?php namespace ApprovalTests;

use ApprovalTests\Writers\Writer;
use ApprovalTests\Writers\TextWriter;
use ApprovalTests\Reporters\Reporter;
use ApprovalTests\Reporters\PHPUnitReporter;
use ApprovalTests\Reporters\OpenReceivedFileReporter;
use ApprovalTests\Namers\PHPUnitNamer;

class Approvals
{
    private static $reporter = null;
    private static $testDirectory = '';

    public static function approveString($received)
    {
        self::approve(new TextWriter($received, 'txt'), new PHPUnitNamer(), self::getReporter('txt'));
    }

    public static function getReporter($extensionWithoutDot)
    {
        if (is_null(self::$reporter)) {
            switch ($extensionWithoutDot) {
            case 'html':
            case 'pdf':
                return new OpenReceivedFileReporter();
                break;
            default:
                return new PHPUnitReporter();
            }
        }
        return self::$reporter;
    }

    public static function useReporter(Reporter $reporter)
    {
        self::$reporter = $reporter;
    }

    public static function approve(Writer $writer, $namer, Reporter $reporter)
    {
        $extension = $writer->getExtensionWithoutDot();
        $approvedFilename = $namer->getApprovedFile($extension);
        if (!file_exists($approvedFilename)) {
            $writer->write($namer->getApprovedFile($extension));
        } 
        $approvedContents = file_get_contents($approvedFilename);

        $receivedFilename = $writer->write($namer->getReceivedFile($extension));
        $receivedContents = file_get_contents($receivedFilename);

        if ($approvedContents === $receivedContents) {
            unlink($receivedFilename);
        } else {
            $reporter->report($approvedContents, $receivedContents);
        }
    }

    public static function approveTransformedList(array $list, $callbackObject, $methodName)
    {
        $string = '';
        foreach ($list as $item) {
            $string .= $item . ' -> ' . $callbackObject->$methodName($item) . "\n";
        }
        self::approveString($string);
    }

    public static function approveList(array $list)
    {
        $string = '';
        foreach ($list as $key => $item) {
            $string .= '[' . $key . '] -> ' . $item . "\n";
        }
        self::approveString($string);
    }

    public static function approveHtml($html)
    {
        self::approve(new TextWriter($html, 'html'), new PHPUnitNamer(), self::getReporter('html'));
    }

    public static function approvePdf(Zend_Pdf $pdf)
    {
        self::approve(new ZendPdfWriter($pdf), new PHPUnitNamer(), self::getReporter('pdf'));
    }
}
