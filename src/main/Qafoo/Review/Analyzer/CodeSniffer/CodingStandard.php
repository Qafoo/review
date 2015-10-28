<?php

namespace Qafoo\Review\Analyzer\CodeSniffer;

class CodingStandard extends PHP_CodeSniffer_Standards_CodingStandard
{
    public function getIncludedSniffs()
    {
        return array(
            'Generic/Sniffs/CodeAnalysis/EmptyStatementSniff.php',
            'Generic/Sniffs/CodeAnalysis/JumbledIncrementerSniff.php',
            'Generic/Sniffs/CodeAnalysis/UnconditionalIfStatementSniff.php',
            'Generic/Sniffs/Files/OneClassPerFileSniff.php',
            'Generic/Sniffs/Files/OneInterfacePerFileSniff.php',
            'Generic/Sniffs/Functions/CallTimePassByReferenceSniff.php',
            'Generic/Sniffs/Metrics/NestingLevelSniff.php',
            'Generic/Sniffs/PHP/DeprecatedFunctionsSniff.php',
            'Generic/Sniffs/PHP/NoSilencedErrorsSniff.php',
            'PSR1/Sniffs/Files/SideEffectsSniff.php',
            'Squiz/Sniffs/PHP/CommentedOutCodeSniff.php',
            'Squiz/Sniffs/PHP/DiscouragedFunctionsSniff.php',
            'Squiz/Sniffs/PHP/EvalSniff.php',
            'Squiz/Sniffs/PHP/GlobalKeywordSniff.php',
            'Squiz/Sniffs/Scope/StaticThisUsageSniff.php',
            // extend /usr/share/php/PHP/CodeSniffer/Standards/Squiz/Sniffs/PHP/ForbiddenFunctionsSniff.php
        );
    }

    public function getExcludedSniffs()
    {
        return array();
    }
}
