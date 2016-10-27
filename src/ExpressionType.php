<?php
/**
 * Created by PhpStorm.
 * User: huyanping
 * Date: 2016/10/27
 * Time: 上午11:18
 */

namespace Jenner\SQL\Parser;


class ExpressionType
{
    const USER_VARIABLE = "user_variable";
    const SESSION_VARIABLE = "session_variable";
    const GLOBAL_VARIABLE = "global_variable";
    const LOCAL_VARIABLE = "local_variable";

    const COLREF = "colref";
    const RESERVED = "reserved";
    const CONSTANT = "const";

    const AGGREGATE_FUNCTION = "aggregate_function";
    const SIMPLE_FUNCTION = "function";

    const EXPRESSION = "expression";
    const BRACKET_EXPRESSION = "bracket_expression";
    const TABLE_EXPRESSION = "table_expression";

    const SUBQUERY = "subquery";
    const IN_LIST = "in-list";
    const OPERATOR = "operator";
    const SIGN = "sign";
    const RECORD = "record";

    const MATCH_ARGUMENTS = "match-arguments";
    const MATCH_MODE = "match-mode";

    const ALIAS = "alias";
    const POSITION = "pos";

    const TEMPORARY_TABLE = "temporary_table";
    const TABLE = "table";
    const VIEW = "view";
    const DATABASE = "database";
    const SCHEMA = "schema";
}