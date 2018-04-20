<?php

/**
 * This file is part of the Symfony-coding-standard (phpcs standard)
 *
 * PHP version 5
 *
 * @category PHP
 * @package  Symfony-coding-standard
 * @author   Authors <Symfony-coding-standard@djoos.github.com>
 * @license  http://spdx.org/licenses/MIT MIT License
 * @link     https://github.com/djoos/Symfony-coding-standard
 */

namespace Symfony\Sniffs\Functions;

use PHP_CodeSniffer\Files\File;
use PHP_CodeSniffer\Sniffs\Sniff;
use PHP_CodeSniffer\Util\Tokens;

/**
 * Checks whether functions are defined on one line.
 *
 * @category PHP
 * @package  Symfony-coding-standard
 * @author   wicliff wolda <wicliff.wolda@gmail.com>
 * @license  http://spdx.org/licenses/MIT MIT License
 * @link     http://pear.php.net/package/PHP_CodeSniffer
 */
class ArgumentsSniff implements Sniff
{
    /**
     * Registers the tokens that this sniff wants to listen for.
     *
     * @return array
     */
    public function register()
    {
        return array(
            T_FUNCTION,
        );
    }

    /**
     * Called when one of the token types that this sniff is listening for
     * is found.
     *
     * @param File $phpcsFile The file where the token was found.
     * @param int  $stackPtr  The position of the current token
     *                        in the stack passed in $tokens.
     *
     * @return void|int Optionally returns a stack pointer. The sniff will not be
     *                  called again on the current file until the returned stack
     *                  pointer is reached. Return (count($tokens) + 1) to skip
     *                  the rest of the file.
     */
    public function process(File $phpcsFile, $stackPtr)
    {
        $tokens = $phpcsFile->getTokens();
        $function = $tokens[$stackPtr];

        $parenthesis_opener = $function['parenthesis_opener'];
        $parenthesis_closer = $function['parenthesis_closer'];
        $openerLine         = $tokens[$parenthesis_opener]['line'];
        $closerLine         = $tokens[$function['parenthesis_closer']]['line'];

        if ($openerLine !== $closerLine) {
            $error = 'Declare all the arguments on the same line ';
            $error .= 'as the method/function name, ';
            $error .= 'no matter how many arguments there are.';

            $commentPtr = $phpcsFile->findNext(
                Tokens::$commentTokens,
                $parenthesis_opener,
                $parenthesis_closer
            );

            if ($commentPtr !== false) {
                $phpcsFile->addError(
                    $error,
                    $stackPtr,
                    'Invalid'
                );

                return;
            }

            $fixable = $phpcsFile->addFixableError(
                $error,
                $stackPtr,
                'Invalid'
            );

            if (false === $fixable) {
                return;
            }

            $whitespacePtr = $phpcsFile->findNext(
                T_WHITESPACE,
                $parenthesis_opener,
                $function['parenthesis_closer']
            );

            $phpcsFile->fixer->beginChangeset();
            while (false !== $whitespacePtr) {
                if ("\n" === $tokens[$whitespacePtr]['content']) {
                    $phpcsFile->fixer->replaceToken($whitespacePtr, ' ');
                }
                $whitespacePtr = $phpcsFile->findNext(
                    T_WHITESPACE,
                    $whitespacePtr + 1,
                    $function['parenthesis_closer']
                );
            }
            $phpcsFile->fixer->endChangeset();
        }
    }

}
