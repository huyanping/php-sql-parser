<?php
/**
 * Created by PhpStorm.
 * User: huyanping
 * Date: 2016/10/27
 * Time: 上午11:34
 */

namespace Jenner\SQL\Parser;


use Jenner\SQL\Parser\Exception\InvalidParameterException;

class Lexer extends ParserUtils
{
    private $splitters;

    public function __construct()
    {
        $this->splitters = new LexerSplitter();
    }

    public function split($sql)
    {
        if (!is_string($sql)) {
            throw new InvalidParameterException($sql);
        }

        $tokens = array();
        $token = "";

        $splitLen = $this->splitters->getMaxLengthOfSplitter();
        $found = false;
        $len = strlen($sql);
        $pos = 0;

        while ($pos < $len) {

            for ($i = $splitLen; $i > 0; $i--) {
                $substr = substr($sql, $pos, $i);
                if ($this->splitters->isSplitter($substr)) {

                    if ($token !== "") {
                        $tokens[] = $token;
                    }

                    $tokens[] = $substr;
                    $pos += $i;
                    $token = "";

                    continue 2;
                }
            }

            $token .= $sql[$pos];
            $pos++;
        }

        if ($token !== "") {
            $tokens[] = $token;
        }

        $tokens = $this->concatEscapeSequences($tokens);
        $tokens = $this->balanceBackticks($tokens);
        $tokens = $this->concatColReferences($tokens);
        $tokens = $this->balanceParenthesis($tokens);
        $tokens = $this->balanceMultilineComments($tokens);
        $tokens = $this->concatInlineComments($tokens);
        $tokens = $this->concatUserDefinedVariables($tokens);
        return $tokens;
    }

    private function concatUserDefinedVariables($tokens)
    {
        $i = 0;
        $cnt = count($tokens);
        $userdef = false;

        while ($i < $cnt) {

            if (!isset($tokens[$i])) {
                $i++;
                continue;
            }

            $token = $tokens[$i];

            if ($userdef !== false) {
                $tokens[$userdef] .= $token;
                unset($tokens[$i]);
                if ($token !== "@") {
                    $userdef = false;
                }
            }

            if ($userdef === false && $token === "@") {
                $userdef = $i;
            }

            $i++;
        }

        return array_values($tokens);
    }

    private function concatInlineComments($tokens)
    {

        $i = 0;
        $cnt = count($tokens);
        $comment = false;

        while ($i < $cnt) {

            if (!isset($tokens[$i])) {
                $i++;
                continue;
            }

            $token = $tokens[$i];

            if ($comment !== false) {
                if ($token === "\n" || $token === "\r\n") {
                    $comment = false;
                } else {
                    unset($tokens[$i]);
                    $tokens[$comment] .= $token;
                }
            }

            if (($comment === false) && ($token === "-")) {
                if (isset($tokens[$i + 1]) && $tokens[$i + 1] === "-") {
                    $comment = $i;
                    $tokens[$i] = "--";
                    $i++;
                    unset($tokens[$i]);
                    continue;
                }
            }

            $i++;
        }

        return array_values($tokens);
    }

    private function balanceMultilineComments($tokens)
    {

        $i = 0;
        $cnt = count($tokens);
        $comment = false;

        while ($i < $cnt) {

            if (!isset($tokens[$i])) {
                $i++;
                continue;
            }

            $token = $tokens[$i];

            if ($comment !== false) {
                unset($tokens[$i]);
                $tokens[$comment] .= $token;
                if ($token === "*" && isset($tokens[$i + 1]) && $tokens[$i + 1] === "/") {
                    unset($tokens[$i + 1]);
                    $tokens[$comment] .= "/";
                    $comment = false;
                }
            }

            if (($comment === false) && ($token === "/")) {
                if (isset($tokens[$i + 1]) && $tokens[$i + 1] === "*") {
                    $comment = $i;
                    $tokens[$i] = "/*";
                    $i++;
                    unset($tokens[$i]);
                    continue;
                }
            }

            $i++;
        }
        return array_values($tokens);
    }

    private function isBacktick($token)
    {
        return ($token === "'" || $token === "\"" || $token === "`");
    }

    private function balanceBackticks($tokens)
    {
        $i = 0;
        $cnt = count($tokens);
        while ($i < $cnt) {

            if (!isset($tokens[$i])) {
                $i++;
                continue;
            }

            $token = $tokens[$i];

            if ($this->isBacktick($token)) {
                $tokens = $this->balanceCharacter($tokens, $i, $token);
            }

            $i++;
        }

        return $tokens;
    }

    # backticks are not balanced within one token, so we have
    # to re-combine some tokens
    private function balanceCharacter($tokens, $idx, $char)
    {

        $token_count = count($tokens);
        $i = $idx + 1;
        while ($i < $token_count) {

            if (!isset($tokens[$i])) {
                $i++;
                continue;
            }

            $token = $tokens[$i];
            $tokens[$idx] .= $token;
            unset($tokens[$i]);

            if ($token === $char) {
                break;
            }

            $i++;
        }
        return array_values($tokens);
    }

    /*
     * does the token ends with dot?
     * concat it with the next token
     *
     * does the token starts with a dot?
     * concat it with the previous token
     */
    private function concatColReferences($tokens)
    {

        $cnt = count($tokens);
        $i = 0;
        while ($i < $cnt) {

            if (!isset($tokens[$i])) {
                $i++;
                continue;
            }

            if ($tokens[$i][0] === ".") {

                // concat the previous tokens, till the token has been changed
                $k = $i - 1;
                $len = strlen($tokens[$i]);
                while (($k >= 0) && ($len == strlen($tokens[$i]))) {
                    if (!isset($tokens[$k])) { # FIXME: this can be wrong if we have schema . table . column
                        $k--;
                        continue;
                    }
                    $tokens[$i] = $tokens[$k] . $tokens[$i];
                    unset($tokens[$k]);
                    $k--;
                }
            }

            if ($this->endsWith($tokens[$i], '.')) {

                // concat the next tokens, till the token has been changed
                $k = $i + 1;
                $len = strlen($tokens[$i]);
                while (($k < $cnt) && ($len == strlen($tokens[$i]))) {
                    if (!isset($tokens[$k])) {
                        $k++;
                        continue;
                    }
                    $tokens[$i] .= $tokens[$k];
                    unset($tokens[$k]);
                    $k++;
                }
            }

            $i++;
        }

        return array_values($tokens);
    }

    private function concatEscapeSequences($tokens)
    {
        $tokenCount = count($tokens);
        $i = 0;
        while ($i < $tokenCount) {

            if ($this->endsWith($tokens[$i], "\\")) {
                $i++;
                if (isset($tokens[$i])) {
                    $tokens[$i - 1] .= $tokens[$i];
                    unset($tokens[$i]);
                }
            }
            $i++;
        }
        return array_values($tokens);
    }

    private function balanceParenthesis($tokens)
    {
        $token_count = count($tokens);
        $i = 0;
        while ($i < $token_count) {
            if ($tokens[$i] !== '(') {
                $i++;
                continue;
            }
            $count = 1;
            for ($n = $i + 1; $n < $token_count; $n++) {
                $token = $tokens[$n];
                if ($token === '(') {
                    $count++;
                }
                if ($token === ')') {
                    $count--;
                }
                $tokens[$i] .= $token;
                unset($tokens[$n]);
                if ($count === 0) {
                    $n++;
                    break;
                }
            }
            $i = $n;
        }
        return array_values($tokens);
    }
}