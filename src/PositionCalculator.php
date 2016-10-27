<?php
/**
 * Created by PhpStorm.
 * User: huyanping
 * Date: 2016/10/27
 * Time: 上午11:35
 */

namespace Jenner\SQL\Parser;


use Jenner\SQL\Parser\Exception\UnableToCalculatePositionException;

class PositionCalculator extends ParserUtils
{
    private static $allowedOnOperator = array("\t", "\n", "\r", " ", ",", "(", ")", "_", "'", "\"");
    private static $allowedOnOther = array("\t", "\n", "\r", " ", ",", "(", ")", "<", ">", "*", "+", "-", "/", "|",
        "&", "=", "!", ";");

    private function printPos($text, $sql, $charPos, $key, $parsed, $backtracking)
    {
        if (!isset($_ENV['DEBUG'])) {
            return;
        }

        $spaces = "";
        $caller = debug_backtrace();
        $i = 1;
        while ($caller[$i]['function'] === 'lookForBaseExpression') {
            $spaces .= "   ";
            $i++;
        }
        $holdem = substr($sql, 0, $charPos) . "^" . substr($sql, $charPos);
        echo $spaces . $text . " key:" . $key . "  parsed:" . $parsed . " back:" . serialize($backtracking) . " "
            . $holdem . "\n";
    }

    public function setPositionsWithinSQL($sql, $parsed)
    {
        $charPos = 0;
        $backtracking = array();
        $this->lookForBaseExpression($sql, $charPos, $parsed, 0, $backtracking);
        return $parsed;
    }

    private function findPositionWithinString($sql, $value, $expr_type)
    {

        $offset = 0;
        $ok = false;
        while (true) {

            $pos = strpos($sql, $value, $offset);
            if ($pos === false) {
                break;
            }

            $before = "";
            if ($pos > 0) {
                $before = $sql[$pos - 1];
            }

            $after = "";
            if (isset($sql[$pos + strlen($value)])) {
                $after = $sql[$pos + strlen($value)];
            }

            # if we have an operator, it should be surrounded by
            # whitespace, comma, parenthesis, digit or letter, end_of_string
            # an operator should not be surrounded by another operator

            if ($expr_type === 'operator') {

                $ok = ($before === "" || in_array($before, self::$allowedOnOperator, true))
                    || (strtolower($before) >= 'a' && strtolower($before) <= 'z')
                    || ($before >= '0' && $before <= '9');
                $ok = $ok
                    && ($after === "" || in_array($after, self::$allowedOnOperator, true)
                        || (strtolower($after) >= 'a' && strtolower($after) <= 'z')
                        || ($after >= '0' && $after <= '9') || ($after === '?') || ($after === '@'));

                if (!$ok) {
                    $offset = $pos + 1;
                    continue;
                }

                break;
            }

            # in all other cases we accept
            # whitespace, comma, operators, parenthesis and end_of_string

            $ok = ($before === "" || in_array($before, self::$allowedOnOther, true));
            $ok = $ok && ($after === "" || in_array($after, self::$allowedOnOther, true));

            if ($ok) {
                break;
            }

            $offset = $pos + 1;
        }

        return $pos;
    }

    private function lookForBaseExpression($sql, &$charPos, &$parsed, $key, &$backtracking)
    {
        if (!is_numeric($key)) {
            if (($key === 'UNION' || $key === 'UNION ALL')
                || ($key === 'expr_type' && $parsed === ExpressionType::EXPRESSION)
                || ($key === 'expr_type' && $parsed === ExpressionType::SUBQUERY)
                || ($key === 'expr_type' && $parsed === ExpressionType::BRACKET_EXPRESSION)
                || ($key === 'expr_type' && $parsed === ExpressionType::TABLE_EXPRESSION)
                || ($key === 'expr_type' && $parsed === ExpressionType::RECORD)
                || ($key === 'expr_type' && $parsed === ExpressionType::IN_LIST)
                || ($key === 'expr_type' && $parsed === ExpressionType::MATCH_ARGUMENTS)
                || ($key === 'alias' && $parsed !== false)
            ) {
                # we hold the current position and come back after the next base_expr
                # we do this, because the next base_expr contains the complete expression/subquery/record
                # and we have to look into it too
                $backtracking[] = $charPos;

            } elseif (($key === 'ref_clause' || $key === 'columns') && $parsed !== false) {
                # we hold the current position and come back after n base_expr(s)
                # there is an array of sub-elements before (!) the base_expr clause of the current element
                # so we go through the sub-elements and must come at the end
                $backtracking[] = $charPos;
                for ($i = 1; $i < count($parsed); $i++) {
                    $backtracking[] = false; # backtracking only after n base_expr!
                }
            } elseif ($key === 'sub_tree' && $parsed !== false) {
                # we prevent wrong backtracking on subtrees (too much array_pop())
                # there is an array of sub-elements after(!) the base_expr clause of the current element
                # so we go through the sub-elements and must not come back at the end
                for ($i = 1; $i < count($parsed); $i++) {
                    $backtracking[] = false;
                }
            } else {
                # move the current pos after the keyword
                # SELECT, WHERE, INSERT etc.
                if (in_array($key, parent::$reserved)) {
                    $charPos = stripos($sql, $key, $charPos);
                    $charPos += strlen($key);
                }
            }
        }

        if (!is_array($parsed)) {
            return;
        }

        foreach ($parsed as $key => $value) {
            if ($key === 'base_expr') {

                #$this->printPos("0", $sql, $charPos, $key, $value, $backtracking);

                $subject = substr($sql, $charPos);
                $pos = $this->findPositionWithinString($subject, $value,
                    isset($parsed['expr_type']) ? $parsed['expr_type'] : 'alias');
                if ($pos === false) {
                    throw new UnableToCalculatePositionException($value, $subject);
                }

                $parsed['position'] = $charPos + $pos;
                $charPos += $pos + strlen($value);

                #$this->printPos("1", $sql, $charPos, $key, $value, $backtracking);

                $oldPos = array_pop($backtracking);
                if (isset($oldPos) && $oldPos !== false) {
                    $charPos = $oldPos;
                }

                #$this->printPos("2", $sql, $charPos, $key, $value, $backtracking);

            } else {
                $this->lookForBaseExpression($sql, $charPos, $parsed[$key], $key, $backtracking);
            }
        }
    }
}