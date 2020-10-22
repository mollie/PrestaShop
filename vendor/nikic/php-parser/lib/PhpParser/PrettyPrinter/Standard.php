<?php

namespace MolliePrefix\PhpParser\PrettyPrinter;

use MolliePrefix\PhpParser\Node;
use MolliePrefix\PhpParser\Node\Expr;
use MolliePrefix\PhpParser\Node\Expr\AssignOp;
use MolliePrefix\PhpParser\Node\Expr\BinaryOp;
use MolliePrefix\PhpParser\Node\Expr\Cast;
use MolliePrefix\PhpParser\Node\Name;
use MolliePrefix\PhpParser\Node\Scalar;
use MolliePrefix\PhpParser\Node\Scalar\MagicConst;
use MolliePrefix\PhpParser\Node\Stmt;
use MolliePrefix\PhpParser\PrettyPrinterAbstract;
class Standard extends \MolliePrefix\PhpParser\PrettyPrinterAbstract
{
    // Special nodes
    protected function pParam(\MolliePrefix\PhpParser\Node\Param $node)
    {
        return ($node->type ? $this->pType($node->type) . ' ' : '') . ($node->byRef ? '&' : '') . ($node->variadic ? '...' : '') . '$' . $node->name . ($node->default ? ' = ' . $this->p($node->default) : '');
    }
    protected function pArg(\MolliePrefix\PhpParser\Node\Arg $node)
    {
        return ($node->byRef ? '&' : '') . ($node->unpack ? '...' : '') . $this->p($node->value);
    }
    protected function pConst(\MolliePrefix\PhpParser\Node\Const_ $node)
    {
        return $node->name . ' = ' . $this->p($node->value);
    }
    protected function pNullableType(\MolliePrefix\PhpParser\Node\NullableType $node)
    {
        return '?' . $this->pType($node->type);
    }
    // Names
    protected function pName(\MolliePrefix\PhpParser\Node\Name $node)
    {
        return \implode('\\', $node->parts);
    }
    protected function pName_FullyQualified(\MolliePrefix\PhpParser\Node\Name\FullyQualified $node)
    {
        return '\\' . \implode('\\', $node->parts);
    }
    protected function pName_Relative(\MolliePrefix\PhpParser\Node\Name\Relative $node)
    {
        return 'namespace\\' . \implode('\\', $node->parts);
    }
    // Magic Constants
    protected function pScalar_MagicConst_Class(\MolliePrefix\PhpParser\Node\Scalar\MagicConst\Class_ $node)
    {
        return '__CLASS__';
    }
    protected function pScalar_MagicConst_Dir(\MolliePrefix\PhpParser\Node\Scalar\MagicConst\Dir $node)
    {
        return '__DIR__';
    }
    protected function pScalar_MagicConst_File(\MolliePrefix\PhpParser\Node\Scalar\MagicConst\File $node)
    {
        return '__FILE__';
    }
    protected function pScalar_MagicConst_Function(\MolliePrefix\PhpParser\Node\Scalar\MagicConst\Function_ $node)
    {
        return '__FUNCTION__';
    }
    protected function pScalar_MagicConst_Line(\MolliePrefix\PhpParser\Node\Scalar\MagicConst\Line $node)
    {
        return '__LINE__';
    }
    protected function pScalar_MagicConst_Method(\MolliePrefix\PhpParser\Node\Scalar\MagicConst\Method $node)
    {
        return '__METHOD__';
    }
    protected function pScalar_MagicConst_Namespace(\MolliePrefix\PhpParser\Node\Scalar\MagicConst\Namespace_ $node)
    {
        return '__NAMESPACE__';
    }
    protected function pScalar_MagicConst_Trait(\MolliePrefix\PhpParser\Node\Scalar\MagicConst\Trait_ $node)
    {
        return '__TRAIT__';
    }
    // Scalars
    protected function pScalar_String(\MolliePrefix\PhpParser\Node\Scalar\String_ $node)
    {
        $kind = $node->getAttribute('kind', \MolliePrefix\PhpParser\Node\Scalar\String_::KIND_SINGLE_QUOTED);
        switch ($kind) {
            case \MolliePrefix\PhpParser\Node\Scalar\String_::KIND_NOWDOC:
                $label = $node->getAttribute('docLabel');
                if ($label && !$this->containsEndLabel($node->value, $label)) {
                    if ($node->value === '') {
                        return $this->pNoIndent("<<<'{$label}'\n{$label}") . $this->docStringEndToken;
                    }
                    return $this->pNoIndent("<<<'{$label}'\n{$node->value}\n{$label}") . $this->docStringEndToken;
                }
            /* break missing intentionally */
            case \MolliePrefix\PhpParser\Node\Scalar\String_::KIND_SINGLE_QUOTED:
                return '\'' . $this->pNoIndent(\addcslashes($node->value, '\'\\')) . '\'';
            case \MolliePrefix\PhpParser\Node\Scalar\String_::KIND_HEREDOC:
                $label = $node->getAttribute('docLabel');
                if ($label && !$this->containsEndLabel($node->value, $label)) {
                    if ($node->value === '') {
                        return $this->pNoIndent("<<<{$label}\n{$label}") . $this->docStringEndToken;
                    }
                    $escaped = $this->escapeString($node->value, null);
                    return $this->pNoIndent("<<<{$label}\n" . $escaped . "\n{$label}") . $this->docStringEndToken;
                }
            /* break missing intentionally */
            case \MolliePrefix\PhpParser\Node\Scalar\String_::KIND_DOUBLE_QUOTED:
                return '"' . $this->escapeString($node->value, '"') . '"';
        }
        throw new \Exception('Invalid string kind');
    }
    protected function pScalar_Encapsed(\MolliePrefix\PhpParser\Node\Scalar\Encapsed $node)
    {
        if ($node->getAttribute('kind') === \MolliePrefix\PhpParser\Node\Scalar\String_::KIND_HEREDOC) {
            $label = $node->getAttribute('docLabel');
            if ($label && !$this->encapsedContainsEndLabel($node->parts, $label)) {
                if (\count($node->parts) === 1 && $node->parts[0] instanceof \MolliePrefix\PhpParser\Node\Scalar\EncapsedStringPart && $node->parts[0]->value === '') {
                    return $this->pNoIndent("<<<{$label}\n{$label}") . $this->docStringEndToken;
                }
                return $this->pNoIndent("<<<{$label}\n" . $this->pEncapsList($node->parts, null) . "\n{$label}") . $this->docStringEndToken;
            }
        }
        return '"' . $this->pEncapsList($node->parts, '"') . '"';
    }
    protected function pScalar_LNumber(\MolliePrefix\PhpParser\Node\Scalar\LNumber $node)
    {
        if ($node->value === -\PHP_INT_MAX - 1) {
            // PHP_INT_MIN cannot be represented as a literal,
            // because the sign is not part of the literal
            return '(-' . \PHP_INT_MAX . '-1)';
        }
        $kind = $node->getAttribute('kind', \MolliePrefix\PhpParser\Node\Scalar\LNumber::KIND_DEC);
        if (\MolliePrefix\PhpParser\Node\Scalar\LNumber::KIND_DEC === $kind) {
            return (string) $node->value;
        }
        $sign = $node->value < 0 ? '-' : '';
        $str = (string) $node->value;
        switch ($kind) {
            case \MolliePrefix\PhpParser\Node\Scalar\LNumber::KIND_BIN:
                return $sign . '0b' . \base_convert($str, 10, 2);
            case \MolliePrefix\PhpParser\Node\Scalar\LNumber::KIND_OCT:
                return $sign . '0' . \base_convert($str, 10, 8);
            case \MolliePrefix\PhpParser\Node\Scalar\LNumber::KIND_HEX:
                return $sign . '0x' . \base_convert($str, 10, 16);
        }
        throw new \Exception('Invalid number kind');
    }
    protected function pScalar_DNumber(\MolliePrefix\PhpParser\Node\Scalar\DNumber $node)
    {
        if (!\is_finite($node->value)) {
            if ($node->value === \INF) {
                return '\\INF';
            } elseif ($node->value === -\INF) {
                return '-\\INF';
            } else {
                return '\\NAN';
            }
        }
        // Try to find a short full-precision representation
        $stringValue = \sprintf('%.16G', $node->value);
        if ($node->value !== (double) $stringValue) {
            $stringValue = \sprintf('%.17G', $node->value);
        }
        // %G is locale dependent and there exists no locale-independent alternative. We don't want
        // mess with switching locales here, so let's assume that a comma is the only non-standard
        // decimal separator we may encounter...
        $stringValue = \str_replace(',', '.', $stringValue);
        // ensure that number is really printed as float
        return \preg_match('/^-?[0-9]+$/', $stringValue) ? $stringValue . '.0' : $stringValue;
    }
    // Assignments
    protected function pExpr_Assign(\MolliePrefix\PhpParser\Node\Expr\Assign $node)
    {
        return $this->pInfixOp('Expr_Assign', $node->var, ' = ', $node->expr);
    }
    protected function pExpr_AssignRef(\MolliePrefix\PhpParser\Node\Expr\AssignRef $node)
    {
        return $this->pInfixOp('Expr_AssignRef', $node->var, ' =& ', $node->expr);
    }
    protected function pExpr_AssignOp_Plus(\MolliePrefix\PhpParser\Node\Expr\AssignOp\Plus $node)
    {
        return $this->pInfixOp('Expr_AssignOp_Plus', $node->var, ' += ', $node->expr);
    }
    protected function pExpr_AssignOp_Minus(\MolliePrefix\PhpParser\Node\Expr\AssignOp\Minus $node)
    {
        return $this->pInfixOp('Expr_AssignOp_Minus', $node->var, ' -= ', $node->expr);
    }
    protected function pExpr_AssignOp_Mul(\MolliePrefix\PhpParser\Node\Expr\AssignOp\Mul $node)
    {
        return $this->pInfixOp('Expr_AssignOp_Mul', $node->var, ' *= ', $node->expr);
    }
    protected function pExpr_AssignOp_Div(\MolliePrefix\PhpParser\Node\Expr\AssignOp\Div $node)
    {
        return $this->pInfixOp('Expr_AssignOp_Div', $node->var, ' /= ', $node->expr);
    }
    protected function pExpr_AssignOp_Concat(\MolliePrefix\PhpParser\Node\Expr\AssignOp\Concat $node)
    {
        return $this->pInfixOp('Expr_AssignOp_Concat', $node->var, ' .= ', $node->expr);
    }
    protected function pExpr_AssignOp_Mod(\MolliePrefix\PhpParser\Node\Expr\AssignOp\Mod $node)
    {
        return $this->pInfixOp('Expr_AssignOp_Mod', $node->var, ' %= ', $node->expr);
    }
    protected function pExpr_AssignOp_BitwiseAnd(\MolliePrefix\PhpParser\Node\Expr\AssignOp\BitwiseAnd $node)
    {
        return $this->pInfixOp('Expr_AssignOp_BitwiseAnd', $node->var, ' &= ', $node->expr);
    }
    protected function pExpr_AssignOp_BitwiseOr(\MolliePrefix\PhpParser\Node\Expr\AssignOp\BitwiseOr $node)
    {
        return $this->pInfixOp('Expr_AssignOp_BitwiseOr', $node->var, ' |= ', $node->expr);
    }
    protected function pExpr_AssignOp_BitwiseXor(\MolliePrefix\PhpParser\Node\Expr\AssignOp\BitwiseXor $node)
    {
        return $this->pInfixOp('Expr_AssignOp_BitwiseXor', $node->var, ' ^= ', $node->expr);
    }
    protected function pExpr_AssignOp_ShiftLeft(\MolliePrefix\PhpParser\Node\Expr\AssignOp\ShiftLeft $node)
    {
        return $this->pInfixOp('Expr_AssignOp_ShiftLeft', $node->var, ' <<= ', $node->expr);
    }
    protected function pExpr_AssignOp_ShiftRight(\MolliePrefix\PhpParser\Node\Expr\AssignOp\ShiftRight $node)
    {
        return $this->pInfixOp('Expr_AssignOp_ShiftRight', $node->var, ' >>= ', $node->expr);
    }
    protected function pExpr_AssignOp_Pow(\MolliePrefix\PhpParser\Node\Expr\AssignOp\Pow $node)
    {
        return $this->pInfixOp('Expr_AssignOp_Pow', $node->var, ' **= ', $node->expr);
    }
    // Binary expressions
    protected function pExpr_BinaryOp_Plus(\MolliePrefix\PhpParser\Node\Expr\BinaryOp\Plus $node)
    {
        return $this->pInfixOp('Expr_BinaryOp_Plus', $node->left, ' + ', $node->right);
    }
    protected function pExpr_BinaryOp_Minus(\MolliePrefix\PhpParser\Node\Expr\BinaryOp\Minus $node)
    {
        return $this->pInfixOp('Expr_BinaryOp_Minus', $node->left, ' - ', $node->right);
    }
    protected function pExpr_BinaryOp_Mul(\MolliePrefix\PhpParser\Node\Expr\BinaryOp\Mul $node)
    {
        return $this->pInfixOp('Expr_BinaryOp_Mul', $node->left, ' * ', $node->right);
    }
    protected function pExpr_BinaryOp_Div(\MolliePrefix\PhpParser\Node\Expr\BinaryOp\Div $node)
    {
        return $this->pInfixOp('Expr_BinaryOp_Div', $node->left, ' / ', $node->right);
    }
    protected function pExpr_BinaryOp_Concat(\MolliePrefix\PhpParser\Node\Expr\BinaryOp\Concat $node)
    {
        return $this->pInfixOp('Expr_BinaryOp_Concat', $node->left, ' . ', $node->right);
    }
    protected function pExpr_BinaryOp_Mod(\MolliePrefix\PhpParser\Node\Expr\BinaryOp\Mod $node)
    {
        return $this->pInfixOp('Expr_BinaryOp_Mod', $node->left, ' % ', $node->right);
    }
    protected function pExpr_BinaryOp_BooleanAnd(\MolliePrefix\PhpParser\Node\Expr\BinaryOp\BooleanAnd $node)
    {
        return $this->pInfixOp('Expr_BinaryOp_BooleanAnd', $node->left, ' && ', $node->right);
    }
    protected function pExpr_BinaryOp_BooleanOr(\MolliePrefix\PhpParser\Node\Expr\BinaryOp\BooleanOr $node)
    {
        return $this->pInfixOp('Expr_BinaryOp_BooleanOr', $node->left, ' || ', $node->right);
    }
    protected function pExpr_BinaryOp_BitwiseAnd(\MolliePrefix\PhpParser\Node\Expr\BinaryOp\BitwiseAnd $node)
    {
        return $this->pInfixOp('Expr_BinaryOp_BitwiseAnd', $node->left, ' & ', $node->right);
    }
    protected function pExpr_BinaryOp_BitwiseOr(\MolliePrefix\PhpParser\Node\Expr\BinaryOp\BitwiseOr $node)
    {
        return $this->pInfixOp('Expr_BinaryOp_BitwiseOr', $node->left, ' | ', $node->right);
    }
    protected function pExpr_BinaryOp_BitwiseXor(\MolliePrefix\PhpParser\Node\Expr\BinaryOp\BitwiseXor $node)
    {
        return $this->pInfixOp('Expr_BinaryOp_BitwiseXor', $node->left, ' ^ ', $node->right);
    }
    protected function pExpr_BinaryOp_ShiftLeft(\MolliePrefix\PhpParser\Node\Expr\BinaryOp\ShiftLeft $node)
    {
        return $this->pInfixOp('Expr_BinaryOp_ShiftLeft', $node->left, ' << ', $node->right);
    }
    protected function pExpr_BinaryOp_ShiftRight(\MolliePrefix\PhpParser\Node\Expr\BinaryOp\ShiftRight $node)
    {
        return $this->pInfixOp('Expr_BinaryOp_ShiftRight', $node->left, ' >> ', $node->right);
    }
    protected function pExpr_BinaryOp_Pow(\MolliePrefix\PhpParser\Node\Expr\BinaryOp\Pow $node)
    {
        return $this->pInfixOp('Expr_BinaryOp_Pow', $node->left, ' ** ', $node->right);
    }
    protected function pExpr_BinaryOp_LogicalAnd(\MolliePrefix\PhpParser\Node\Expr\BinaryOp\LogicalAnd $node)
    {
        return $this->pInfixOp('Expr_BinaryOp_LogicalAnd', $node->left, ' and ', $node->right);
    }
    protected function pExpr_BinaryOp_LogicalOr(\MolliePrefix\PhpParser\Node\Expr\BinaryOp\LogicalOr $node)
    {
        return $this->pInfixOp('Expr_BinaryOp_LogicalOr', $node->left, ' or ', $node->right);
    }
    protected function pExpr_BinaryOp_LogicalXor(\MolliePrefix\PhpParser\Node\Expr\BinaryOp\LogicalXor $node)
    {
        return $this->pInfixOp('Expr_BinaryOp_LogicalXor', $node->left, ' xor ', $node->right);
    }
    protected function pExpr_BinaryOp_Equal(\MolliePrefix\PhpParser\Node\Expr\BinaryOp\Equal $node)
    {
        return $this->pInfixOp('Expr_BinaryOp_Equal', $node->left, ' == ', $node->right);
    }
    protected function pExpr_BinaryOp_NotEqual(\MolliePrefix\PhpParser\Node\Expr\BinaryOp\NotEqual $node)
    {
        return $this->pInfixOp('Expr_BinaryOp_NotEqual', $node->left, ' != ', $node->right);
    }
    protected function pExpr_BinaryOp_Identical(\MolliePrefix\PhpParser\Node\Expr\BinaryOp\Identical $node)
    {
        return $this->pInfixOp('Expr_BinaryOp_Identical', $node->left, ' === ', $node->right);
    }
    protected function pExpr_BinaryOp_NotIdentical(\MolliePrefix\PhpParser\Node\Expr\BinaryOp\NotIdentical $node)
    {
        return $this->pInfixOp('Expr_BinaryOp_NotIdentical', $node->left, ' !== ', $node->right);
    }
    protected function pExpr_BinaryOp_Spaceship(\MolliePrefix\PhpParser\Node\Expr\BinaryOp\Spaceship $node)
    {
        return $this->pInfixOp('Expr_BinaryOp_Spaceship', $node->left, ' <=> ', $node->right);
    }
    protected function pExpr_BinaryOp_Greater(\MolliePrefix\PhpParser\Node\Expr\BinaryOp\Greater $node)
    {
        return $this->pInfixOp('Expr_BinaryOp_Greater', $node->left, ' > ', $node->right);
    }
    protected function pExpr_BinaryOp_GreaterOrEqual(\MolliePrefix\PhpParser\Node\Expr\BinaryOp\GreaterOrEqual $node)
    {
        return $this->pInfixOp('Expr_BinaryOp_GreaterOrEqual', $node->left, ' >= ', $node->right);
    }
    protected function pExpr_BinaryOp_Smaller(\MolliePrefix\PhpParser\Node\Expr\BinaryOp\Smaller $node)
    {
        return $this->pInfixOp('Expr_BinaryOp_Smaller', $node->left, ' < ', $node->right);
    }
    protected function pExpr_BinaryOp_SmallerOrEqual(\MolliePrefix\PhpParser\Node\Expr\BinaryOp\SmallerOrEqual $node)
    {
        return $this->pInfixOp('Expr_BinaryOp_SmallerOrEqual', $node->left, ' <= ', $node->right);
    }
    protected function pExpr_BinaryOp_Coalesce(\MolliePrefix\PhpParser\Node\Expr\BinaryOp\Coalesce $node)
    {
        return $this->pInfixOp('Expr_BinaryOp_Coalesce', $node->left, ' ?? ', $node->right);
    }
    protected function pExpr_Instanceof(\MolliePrefix\PhpParser\Node\Expr\Instanceof_ $node)
    {
        return $this->pInfixOp('Expr_Instanceof', $node->expr, ' instanceof ', $node->class);
    }
    // Unary expressions
    protected function pExpr_BooleanNot(\MolliePrefix\PhpParser\Node\Expr\BooleanNot $node)
    {
        return $this->pPrefixOp('Expr_BooleanNot', '!', $node->expr);
    }
    protected function pExpr_BitwiseNot(\MolliePrefix\PhpParser\Node\Expr\BitwiseNot $node)
    {
        return $this->pPrefixOp('Expr_BitwiseNot', '~', $node->expr);
    }
    protected function pExpr_UnaryMinus(\MolliePrefix\PhpParser\Node\Expr\UnaryMinus $node)
    {
        if ($node->expr instanceof \MolliePrefix\PhpParser\Node\Expr\UnaryMinus || $node->expr instanceof \MolliePrefix\PhpParser\Node\Expr\PreDec) {
            // Enforce -(-$expr) instead of --$expr
            return '-(' . $this->p($node->expr) . ')';
        }
        return $this->pPrefixOp('Expr_UnaryMinus', '-', $node->expr);
    }
    protected function pExpr_UnaryPlus(\MolliePrefix\PhpParser\Node\Expr\UnaryPlus $node)
    {
        if ($node->expr instanceof \MolliePrefix\PhpParser\Node\Expr\UnaryPlus || $node->expr instanceof \MolliePrefix\PhpParser\Node\Expr\PreInc) {
            // Enforce +(+$expr) instead of ++$expr
            return '+(' . $this->p($node->expr) . ')';
        }
        return $this->pPrefixOp('Expr_UnaryPlus', '+', $node->expr);
    }
    protected function pExpr_PreInc(\MolliePrefix\PhpParser\Node\Expr\PreInc $node)
    {
        return $this->pPrefixOp('Expr_PreInc', '++', $node->var);
    }
    protected function pExpr_PreDec(\MolliePrefix\PhpParser\Node\Expr\PreDec $node)
    {
        return $this->pPrefixOp('Expr_PreDec', '--', $node->var);
    }
    protected function pExpr_PostInc(\MolliePrefix\PhpParser\Node\Expr\PostInc $node)
    {
        return $this->pPostfixOp('Expr_PostInc', $node->var, '++');
    }
    protected function pExpr_PostDec(\MolliePrefix\PhpParser\Node\Expr\PostDec $node)
    {
        return $this->pPostfixOp('Expr_PostDec', $node->var, '--');
    }
    protected function pExpr_ErrorSuppress(\MolliePrefix\PhpParser\Node\Expr\ErrorSuppress $node)
    {
        return $this->pPrefixOp('Expr_ErrorSuppress', '@', $node->expr);
    }
    protected function pExpr_YieldFrom(\MolliePrefix\PhpParser\Node\Expr\YieldFrom $node)
    {
        return $this->pPrefixOp('Expr_YieldFrom', 'yield from ', $node->expr);
    }
    protected function pExpr_Print(\MolliePrefix\PhpParser\Node\Expr\Print_ $node)
    {
        return $this->pPrefixOp('Expr_Print', 'print ', $node->expr);
    }
    // Casts
    protected function pExpr_Cast_Int(\MolliePrefix\PhpParser\Node\Expr\Cast\Int_ $node)
    {
        return $this->pPrefixOp('Expr_Cast_Int', '(int) ', $node->expr);
    }
    protected function pExpr_Cast_Double(\MolliePrefix\PhpParser\Node\Expr\Cast\Double $node)
    {
        return $this->pPrefixOp('Expr_Cast_Double', '(double) ', $node->expr);
    }
    protected function pExpr_Cast_String(\MolliePrefix\PhpParser\Node\Expr\Cast\String_ $node)
    {
        return $this->pPrefixOp('Expr_Cast_String', '(string) ', $node->expr);
    }
    protected function pExpr_Cast_Array(\MolliePrefix\PhpParser\Node\Expr\Cast\Array_ $node)
    {
        return $this->pPrefixOp('Expr_Cast_Array', '(array) ', $node->expr);
    }
    protected function pExpr_Cast_Object(\MolliePrefix\PhpParser\Node\Expr\Cast\Object_ $node)
    {
        return $this->pPrefixOp('Expr_Cast_Object', '(object) ', $node->expr);
    }
    protected function pExpr_Cast_Bool(\MolliePrefix\PhpParser\Node\Expr\Cast\Bool_ $node)
    {
        return $this->pPrefixOp('Expr_Cast_Bool', '(bool) ', $node->expr);
    }
    protected function pExpr_Cast_Unset(\MolliePrefix\PhpParser\Node\Expr\Cast\Unset_ $node)
    {
        return $this->pPrefixOp('Expr_Cast_Unset', '(unset) ', $node->expr);
    }
    // Function calls and similar constructs
    protected function pExpr_FuncCall(\MolliePrefix\PhpParser\Node\Expr\FuncCall $node)
    {
        return $this->pCallLhs($node->name) . '(' . $this->pMaybeMultiline($node->args) . ')';
    }
    protected function pExpr_MethodCall(\MolliePrefix\PhpParser\Node\Expr\MethodCall $node)
    {
        return $this->pDereferenceLhs($node->var) . '->' . $this->pObjectProperty($node->name) . '(' . $this->pMaybeMultiline($node->args) . ')';
    }
    protected function pExpr_StaticCall(\MolliePrefix\PhpParser\Node\Expr\StaticCall $node)
    {
        return $this->pDereferenceLhs($node->class) . '::' . ($node->name instanceof \MolliePrefix\PhpParser\Node\Expr ? $node->name instanceof \MolliePrefix\PhpParser\Node\Expr\Variable ? $this->p($node->name) : '{' . $this->p($node->name) . '}' : $node->name) . '(' . $this->pMaybeMultiline($node->args) . ')';
    }
    protected function pExpr_Empty(\MolliePrefix\PhpParser\Node\Expr\Empty_ $node)
    {
        return 'empty(' . $this->p($node->expr) . ')';
    }
    protected function pExpr_Isset(\MolliePrefix\PhpParser\Node\Expr\Isset_ $node)
    {
        return 'isset(' . $this->pCommaSeparated($node->vars) . ')';
    }
    protected function pExpr_Eval(\MolliePrefix\PhpParser\Node\Expr\Eval_ $node)
    {
        return 'eval(' . $this->p($node->expr) . ')';
    }
    protected function pExpr_Include(\MolliePrefix\PhpParser\Node\Expr\Include_ $node)
    {
        static $map = array(\MolliePrefix\PhpParser\Node\Expr\Include_::TYPE_INCLUDE => 'include', \MolliePrefix\PhpParser\Node\Expr\Include_::TYPE_INCLUDE_ONCE => 'include_once', \MolliePrefix\PhpParser\Node\Expr\Include_::TYPE_REQUIRE => 'require', \MolliePrefix\PhpParser\Node\Expr\Include_::TYPE_REQUIRE_ONCE => 'require_once');
        return $map[$node->type] . ' ' . $this->p($node->expr);
    }
    protected function pExpr_List(\MolliePrefix\PhpParser\Node\Expr\List_ $node)
    {
        return 'list(' . $this->pCommaSeparated($node->items) . ')';
    }
    // Other
    protected function pExpr_Error(\MolliePrefix\PhpParser\Node\Expr\Error $node)
    {
        throw new \LogicException('Cannot pretty-print AST with Error nodes');
    }
    protected function pExpr_Variable(\MolliePrefix\PhpParser\Node\Expr\Variable $node)
    {
        if ($node->name instanceof \MolliePrefix\PhpParser\Node\Expr) {
            return '${' . $this->p($node->name) . '}';
        } else {
            return '$' . $node->name;
        }
    }
    protected function pExpr_Array(\MolliePrefix\PhpParser\Node\Expr\Array_ $node)
    {
        $syntax = $node->getAttribute('kind', $this->options['shortArraySyntax'] ? \MolliePrefix\PhpParser\Node\Expr\Array_::KIND_SHORT : \MolliePrefix\PhpParser\Node\Expr\Array_::KIND_LONG);
        if ($syntax === \MolliePrefix\PhpParser\Node\Expr\Array_::KIND_SHORT) {
            return '[' . $this->pMaybeMultiline($node->items, \true) . ']';
        } else {
            return 'array(' . $this->pMaybeMultiline($node->items, \true) . ')';
        }
    }
    protected function pExpr_ArrayItem(\MolliePrefix\PhpParser\Node\Expr\ArrayItem $node)
    {
        return (null !== $node->key ? $this->p($node->key) . ' => ' : '') . ($node->byRef ? '&' : '') . $this->p($node->value);
    }
    protected function pExpr_ArrayDimFetch(\MolliePrefix\PhpParser\Node\Expr\ArrayDimFetch $node)
    {
        return $this->pDereferenceLhs($node->var) . '[' . (null !== $node->dim ? $this->p($node->dim) : '') . ']';
    }
    protected function pExpr_ConstFetch(\MolliePrefix\PhpParser\Node\Expr\ConstFetch $node)
    {
        return $this->p($node->name);
    }
    protected function pExpr_ClassConstFetch(\MolliePrefix\PhpParser\Node\Expr\ClassConstFetch $node)
    {
        return $this->p($node->class) . '::' . (\is_string($node->name) ? $node->name : $this->p($node->name));
    }
    protected function pExpr_PropertyFetch(\MolliePrefix\PhpParser\Node\Expr\PropertyFetch $node)
    {
        return $this->pDereferenceLhs($node->var) . '->' . $this->pObjectProperty($node->name);
    }
    protected function pExpr_StaticPropertyFetch(\MolliePrefix\PhpParser\Node\Expr\StaticPropertyFetch $node)
    {
        return $this->pDereferenceLhs($node->class) . '::$' . $this->pObjectProperty($node->name);
    }
    protected function pExpr_ShellExec(\MolliePrefix\PhpParser\Node\Expr\ShellExec $node)
    {
        return '`' . $this->pEncapsList($node->parts, '`') . '`';
    }
    protected function pExpr_Closure(\MolliePrefix\PhpParser\Node\Expr\Closure $node)
    {
        return ($node->static ? 'static ' : '') . 'function ' . ($node->byRef ? '&' : '') . '(' . $this->pCommaSeparated($node->params) . ')' . (!empty($node->uses) ? ' use(' . $this->pCommaSeparated($node->uses) . ')' : '') . (null !== $node->returnType ? ' : ' . $this->pType($node->returnType) : '') . ' {' . $this->pStmts($node->stmts) . "\n" . '}';
    }
    protected function pExpr_ClosureUse(\MolliePrefix\PhpParser\Node\Expr\ClosureUse $node)
    {
        return ($node->byRef ? '&' : '') . '$' . $node->var;
    }
    protected function pExpr_New(\MolliePrefix\PhpParser\Node\Expr\New_ $node)
    {
        if ($node->class instanceof \MolliePrefix\PhpParser\Node\Stmt\Class_) {
            $args = $node->args ? '(' . $this->pMaybeMultiline($node->args) . ')' : '';
            return 'new ' . $this->pClassCommon($node->class, $args);
        }
        return 'new ' . $this->p($node->class) . '(' . $this->pMaybeMultiline($node->args) . ')';
    }
    protected function pExpr_Clone(\MolliePrefix\PhpParser\Node\Expr\Clone_ $node)
    {
        return 'clone ' . $this->p($node->expr);
    }
    protected function pExpr_Ternary(\MolliePrefix\PhpParser\Node\Expr\Ternary $node)
    {
        // a bit of cheating: we treat the ternary as a binary op where the ?...: part is the operator.
        // this is okay because the part between ? and : never needs parentheses.
        return $this->pInfixOp('Expr_Ternary', $node->cond, ' ?' . (null !== $node->if ? ' ' . $this->p($node->if) . ' ' : '') . ': ', $node->else);
    }
    protected function pExpr_Exit(\MolliePrefix\PhpParser\Node\Expr\Exit_ $node)
    {
        $kind = $node->getAttribute('kind', \MolliePrefix\PhpParser\Node\Expr\Exit_::KIND_DIE);
        return ($kind === \MolliePrefix\PhpParser\Node\Expr\Exit_::KIND_EXIT ? 'exit' : 'die') . (null !== $node->expr ? '(' . $this->p($node->expr) . ')' : '');
    }
    protected function pExpr_Yield(\MolliePrefix\PhpParser\Node\Expr\Yield_ $node)
    {
        if ($node->value === null) {
            return 'yield';
        } else {
            // this is a bit ugly, but currently there is no way to detect whether the parentheses are necessary
            return '(yield ' . ($node->key !== null ? $this->p($node->key) . ' => ' : '') . $this->p($node->value) . ')';
        }
    }
    // Declarations
    protected function pStmt_Namespace(\MolliePrefix\PhpParser\Node\Stmt\Namespace_ $node)
    {
        if ($this->canUseSemicolonNamespaces) {
            return 'namespace ' . $this->p($node->name) . ';' . "\n" . $this->pStmts($node->stmts, \false);
        } else {
            return 'namespace' . (null !== $node->name ? ' ' . $this->p($node->name) : '') . ' {' . $this->pStmts($node->stmts) . "\n" . '}';
        }
    }
    protected function pStmt_Use(\MolliePrefix\PhpParser\Node\Stmt\Use_ $node)
    {
        return 'use ' . $this->pUseType($node->type) . $this->pCommaSeparated($node->uses) . ';';
    }
    protected function pStmt_GroupUse(\MolliePrefix\PhpParser\Node\Stmt\GroupUse $node)
    {
        return 'use ' . $this->pUseType($node->type) . $this->pName($node->prefix) . '\\{' . $this->pCommaSeparated($node->uses) . '};';
    }
    protected function pStmt_UseUse(\MolliePrefix\PhpParser\Node\Stmt\UseUse $node)
    {
        return $this->pUseType($node->type) . $this->p($node->name) . ($node->name->getLast() !== $node->alias ? ' as ' . $node->alias : '');
    }
    protected function pUseType($type)
    {
        return $type === \MolliePrefix\PhpParser\Node\Stmt\Use_::TYPE_FUNCTION ? 'function ' : ($type === \MolliePrefix\PhpParser\Node\Stmt\Use_::TYPE_CONSTANT ? 'const ' : '');
    }
    protected function pStmt_Interface(\MolliePrefix\PhpParser\Node\Stmt\Interface_ $node)
    {
        return 'interface ' . $node->name . (!empty($node->extends) ? ' extends ' . $this->pCommaSeparated($node->extends) : '') . "\n" . '{' . $this->pStmts($node->stmts) . "\n" . '}';
    }
    protected function pStmt_Class(\MolliePrefix\PhpParser\Node\Stmt\Class_ $node)
    {
        return $this->pClassCommon($node, ' ' . $node->name);
    }
    protected function pStmt_Trait(\MolliePrefix\PhpParser\Node\Stmt\Trait_ $node)
    {
        return 'trait ' . $node->name . "\n" . '{' . $this->pStmts($node->stmts) . "\n" . '}';
    }
    protected function pStmt_TraitUse(\MolliePrefix\PhpParser\Node\Stmt\TraitUse $node)
    {
        return 'use ' . $this->pCommaSeparated($node->traits) . (empty($node->adaptations) ? ';' : ' {' . $this->pStmts($node->adaptations) . "\n" . '}');
    }
    protected function pStmt_TraitUseAdaptation_Precedence(\MolliePrefix\PhpParser\Node\Stmt\TraitUseAdaptation\Precedence $node)
    {
        return $this->p($node->trait) . '::' . $node->method . ' insteadof ' . $this->pCommaSeparated($node->insteadof) . ';';
    }
    protected function pStmt_TraitUseAdaptation_Alias(\MolliePrefix\PhpParser\Node\Stmt\TraitUseAdaptation\Alias $node)
    {
        return (null !== $node->trait ? $this->p($node->trait) . '::' : '') . $node->method . ' as' . (null !== $node->newModifier ? ' ' . \rtrim($this->pModifiers($node->newModifier), ' ') : '') . (null !== $node->newName ? ' ' . $node->newName : '') . ';';
    }
    protected function pStmt_Property(\MolliePrefix\PhpParser\Node\Stmt\Property $node)
    {
        return (0 === $node->flags ? 'var ' : $this->pModifiers($node->flags)) . $this->pCommaSeparated($node->props) . ';';
    }
    protected function pStmt_PropertyProperty(\MolliePrefix\PhpParser\Node\Stmt\PropertyProperty $node)
    {
        return '$' . $node->name . (null !== $node->default ? ' = ' . $this->p($node->default) : '');
    }
    protected function pStmt_ClassMethod(\MolliePrefix\PhpParser\Node\Stmt\ClassMethod $node)
    {
        return $this->pModifiers($node->flags) . 'function ' . ($node->byRef ? '&' : '') . $node->name . '(' . $this->pCommaSeparated($node->params) . ')' . (null !== $node->returnType ? ' : ' . $this->pType($node->returnType) : '') . (null !== $node->stmts ? "\n" . '{' . $this->pStmts($node->stmts) . "\n" . '}' : ';');
    }
    protected function pStmt_ClassConst(\MolliePrefix\PhpParser\Node\Stmt\ClassConst $node)
    {
        return $this->pModifiers($node->flags) . 'const ' . $this->pCommaSeparated($node->consts) . ';';
    }
    protected function pStmt_Function(\MolliePrefix\PhpParser\Node\Stmt\Function_ $node)
    {
        return 'function ' . ($node->byRef ? '&' : '') . $node->name . '(' . $this->pCommaSeparated($node->params) . ')' . (null !== $node->returnType ? ' : ' . $this->pType($node->returnType) : '') . "\n" . '{' . $this->pStmts($node->stmts) . "\n" . '}';
    }
    protected function pStmt_Const(\MolliePrefix\PhpParser\Node\Stmt\Const_ $node)
    {
        return 'const ' . $this->pCommaSeparated($node->consts) . ';';
    }
    protected function pStmt_Declare(\MolliePrefix\PhpParser\Node\Stmt\Declare_ $node)
    {
        return 'declare (' . $this->pCommaSeparated($node->declares) . ')' . (null !== $node->stmts ? ' {' . $this->pStmts($node->stmts) . "\n" . '}' : ';');
    }
    protected function pStmt_DeclareDeclare(\MolliePrefix\PhpParser\Node\Stmt\DeclareDeclare $node)
    {
        return $node->key . '=' . $this->p($node->value);
    }
    // Control flow
    protected function pStmt_If(\MolliePrefix\PhpParser\Node\Stmt\If_ $node)
    {
        return 'if (' . $this->p($node->cond) . ') {' . $this->pStmts($node->stmts) . "\n" . '}' . $this->pImplode($node->elseifs) . (null !== $node->else ? $this->p($node->else) : '');
    }
    protected function pStmt_ElseIf(\MolliePrefix\PhpParser\Node\Stmt\ElseIf_ $node)
    {
        return ' elseif (' . $this->p($node->cond) . ') {' . $this->pStmts($node->stmts) . "\n" . '}';
    }
    protected function pStmt_Else(\MolliePrefix\PhpParser\Node\Stmt\Else_ $node)
    {
        return ' else {' . $this->pStmts($node->stmts) . "\n" . '}';
    }
    protected function pStmt_For(\MolliePrefix\PhpParser\Node\Stmt\For_ $node)
    {
        return 'for (' . $this->pCommaSeparated($node->init) . ';' . (!empty($node->cond) ? ' ' : '') . $this->pCommaSeparated($node->cond) . ';' . (!empty($node->loop) ? ' ' : '') . $this->pCommaSeparated($node->loop) . ') {' . $this->pStmts($node->stmts) . "\n" . '}';
    }
    protected function pStmt_Foreach(\MolliePrefix\PhpParser\Node\Stmt\Foreach_ $node)
    {
        return 'foreach (' . $this->p($node->expr) . ' as ' . (null !== $node->keyVar ? $this->p($node->keyVar) . ' => ' : '') . ($node->byRef ? '&' : '') . $this->p($node->valueVar) . ') {' . $this->pStmts($node->stmts) . "\n" . '}';
    }
    protected function pStmt_While(\MolliePrefix\PhpParser\Node\Stmt\While_ $node)
    {
        return 'while (' . $this->p($node->cond) . ') {' . $this->pStmts($node->stmts) . "\n" . '}';
    }
    protected function pStmt_Do(\MolliePrefix\PhpParser\Node\Stmt\Do_ $node)
    {
        return 'do {' . $this->pStmts($node->stmts) . "\n" . '} while (' . $this->p($node->cond) . ');';
    }
    protected function pStmt_Switch(\MolliePrefix\PhpParser\Node\Stmt\Switch_ $node)
    {
        return 'switch (' . $this->p($node->cond) . ') {' . $this->pStmts($node->cases) . "\n" . '}';
    }
    protected function pStmt_Case(\MolliePrefix\PhpParser\Node\Stmt\Case_ $node)
    {
        return (null !== $node->cond ? 'case ' . $this->p($node->cond) : 'default') . ':' . $this->pStmts($node->stmts);
    }
    protected function pStmt_TryCatch(\MolliePrefix\PhpParser\Node\Stmt\TryCatch $node)
    {
        return 'try {' . $this->pStmts($node->stmts) . "\n" . '}' . $this->pImplode($node->catches) . ($node->finally !== null ? $this->p($node->finally) : '');
    }
    protected function pStmt_Catch(\MolliePrefix\PhpParser\Node\Stmt\Catch_ $node)
    {
        return ' catch (' . $this->pImplode($node->types, '|') . ' $' . $node->var . ') {' . $this->pStmts($node->stmts) . "\n" . '}';
    }
    protected function pStmt_Finally(\MolliePrefix\PhpParser\Node\Stmt\Finally_ $node)
    {
        return ' finally {' . $this->pStmts($node->stmts) . "\n" . '}';
    }
    protected function pStmt_Break(\MolliePrefix\PhpParser\Node\Stmt\Break_ $node)
    {
        return 'break' . ($node->num !== null ? ' ' . $this->p($node->num) : '') . ';';
    }
    protected function pStmt_Continue(\MolliePrefix\PhpParser\Node\Stmt\Continue_ $node)
    {
        return 'continue' . ($node->num !== null ? ' ' . $this->p($node->num) : '') . ';';
    }
    protected function pStmt_Return(\MolliePrefix\PhpParser\Node\Stmt\Return_ $node)
    {
        return 'return' . (null !== $node->expr ? ' ' . $this->p($node->expr) : '') . ';';
    }
    protected function pStmt_Throw(\MolliePrefix\PhpParser\Node\Stmt\Throw_ $node)
    {
        return 'throw ' . $this->p($node->expr) . ';';
    }
    protected function pStmt_Label(\MolliePrefix\PhpParser\Node\Stmt\Label $node)
    {
        return $node->name . ':';
    }
    protected function pStmt_Goto(\MolliePrefix\PhpParser\Node\Stmt\Goto_ $node)
    {
        return 'goto ' . $node->name . ';';
    }
    // Other
    protected function pStmt_Echo(\MolliePrefix\PhpParser\Node\Stmt\Echo_ $node)
    {
        return 'echo ' . $this->pCommaSeparated($node->exprs) . ';';
    }
    protected function pStmt_Static(\MolliePrefix\PhpParser\Node\Stmt\Static_ $node)
    {
        return 'static ' . $this->pCommaSeparated($node->vars) . ';';
    }
    protected function pStmt_Global(\MolliePrefix\PhpParser\Node\Stmt\Global_ $node)
    {
        return 'global ' . $this->pCommaSeparated($node->vars) . ';';
    }
    protected function pStmt_StaticVar(\MolliePrefix\PhpParser\Node\Stmt\StaticVar $node)
    {
        return '$' . $node->name . (null !== $node->default ? ' = ' . $this->p($node->default) : '');
    }
    protected function pStmt_Unset(\MolliePrefix\PhpParser\Node\Stmt\Unset_ $node)
    {
        return 'unset(' . $this->pCommaSeparated($node->vars) . ');';
    }
    protected function pStmt_InlineHTML(\MolliePrefix\PhpParser\Node\Stmt\InlineHTML $node)
    {
        $newline = $node->getAttribute('hasLeadingNewline', \true) ? "\n" : '';
        return '?>' . $this->pNoIndent($newline . $node->value) . '<?php ';
    }
    protected function pStmt_HaltCompiler(\MolliePrefix\PhpParser\Node\Stmt\HaltCompiler $node)
    {
        return '__halt_compiler();' . $node->remaining;
    }
    protected function pStmt_Nop(\MolliePrefix\PhpParser\Node\Stmt\Nop $node)
    {
        return '';
    }
    // Helpers
    protected function pType($node)
    {
        return \is_string($node) ? $node : $this->p($node);
    }
    protected function pClassCommon(\MolliePrefix\PhpParser\Node\Stmt\Class_ $node, $afterClassToken)
    {
        return $this->pModifiers($node->flags) . 'class' . $afterClassToken . (null !== $node->extends ? ' extends ' . $this->p($node->extends) : '') . (!empty($node->implements) ? ' implements ' . $this->pCommaSeparated($node->implements) : '') . "\n" . '{' . $this->pStmts($node->stmts) . "\n" . '}';
    }
    protected function pObjectProperty($node)
    {
        if ($node instanceof \MolliePrefix\PhpParser\Node\Expr) {
            return '{' . $this->p($node) . '}';
        } else {
            return $node;
        }
    }
    protected function pModifiers($modifiers)
    {
        return ($modifiers & \MolliePrefix\PhpParser\Node\Stmt\Class_::MODIFIER_PUBLIC ? 'public ' : '') . ($modifiers & \MolliePrefix\PhpParser\Node\Stmt\Class_::MODIFIER_PROTECTED ? 'protected ' : '') . ($modifiers & \MolliePrefix\PhpParser\Node\Stmt\Class_::MODIFIER_PRIVATE ? 'private ' : '') . ($modifiers & \MolliePrefix\PhpParser\Node\Stmt\Class_::MODIFIER_STATIC ? 'static ' : '') . ($modifiers & \MolliePrefix\PhpParser\Node\Stmt\Class_::MODIFIER_ABSTRACT ? 'abstract ' : '') . ($modifiers & \MolliePrefix\PhpParser\Node\Stmt\Class_::MODIFIER_FINAL ? 'final ' : '');
    }
    protected function pEncapsList(array $encapsList, $quote)
    {
        $return = '';
        foreach ($encapsList as $element) {
            if ($element instanceof \MolliePrefix\PhpParser\Node\Scalar\EncapsedStringPart) {
                $return .= $this->escapeString($element->value, $quote);
            } else {
                $return .= '{' . $this->p($element) . '}';
            }
        }
        return $return;
    }
    protected function escapeString($string, $quote)
    {
        if (null === $quote) {
            // For doc strings, don't escape newlines
            $escaped = \addcslashes($string, "\t\f\v\$\\");
        } else {
            $escaped = \addcslashes($string, "\n\r\t\f\v\$" . $quote . "\\");
        }
        // Escape other control characters
        return \preg_replace_callback('/([\\0-\\10\\16-\\37])(?=([0-7]?))/', function ($matches) {
            $oct = \decoct(\ord($matches[1]));
            if ($matches[2] !== '') {
                // If there is a trailing digit, use the full three character form
                return '\\' . \str_pad($oct, 3, '0', \STR_PAD_LEFT);
            }
            return '\\' . $oct;
        }, $escaped);
    }
    protected function containsEndLabel($string, $label, $atStart = \true, $atEnd = \true)
    {
        $start = $atStart ? '(?:^|[\\r\\n])' : '[\\r\\n]';
        $end = $atEnd ? '(?:$|[;\\r\\n])' : '[;\\r\\n]';
        return \false !== \strpos($string, $label) && \preg_match('/' . $start . $label . $end . '/', $string);
    }
    protected function encapsedContainsEndLabel(array $parts, $label)
    {
        foreach ($parts as $i => $part) {
            $atStart = $i === 0;
            $atEnd = $i === \count($parts) - 1;
            if ($part instanceof \MolliePrefix\PhpParser\Node\Scalar\EncapsedStringPart && $this->containsEndLabel($part->value, $label, $atStart, $atEnd)) {
                return \true;
            }
        }
        return \false;
    }
    protected function pDereferenceLhs(\MolliePrefix\PhpParser\Node $node)
    {
        if ($node instanceof \MolliePrefix\PhpParser\Node\Expr\Variable || $node instanceof \MolliePrefix\PhpParser\Node\Name || $node instanceof \MolliePrefix\PhpParser\Node\Expr\ArrayDimFetch || $node instanceof \MolliePrefix\PhpParser\Node\Expr\PropertyFetch || $node instanceof \MolliePrefix\PhpParser\Node\Expr\StaticPropertyFetch || $node instanceof \MolliePrefix\PhpParser\Node\Expr\FuncCall || $node instanceof \MolliePrefix\PhpParser\Node\Expr\MethodCall || $node instanceof \MolliePrefix\PhpParser\Node\Expr\StaticCall || $node instanceof \MolliePrefix\PhpParser\Node\Expr\Array_ || $node instanceof \MolliePrefix\PhpParser\Node\Scalar\String_ || $node instanceof \MolliePrefix\PhpParser\Node\Expr\ConstFetch || $node instanceof \MolliePrefix\PhpParser\Node\Expr\ClassConstFetch) {
            return $this->p($node);
        } else {
            return '(' . $this->p($node) . ')';
        }
    }
    protected function pCallLhs(\MolliePrefix\PhpParser\Node $node)
    {
        if ($node instanceof \MolliePrefix\PhpParser\Node\Name || $node instanceof \MolliePrefix\PhpParser\Node\Expr\Variable || $node instanceof \MolliePrefix\PhpParser\Node\Expr\ArrayDimFetch || $node instanceof \MolliePrefix\PhpParser\Node\Expr\FuncCall || $node instanceof \MolliePrefix\PhpParser\Node\Expr\MethodCall || $node instanceof \MolliePrefix\PhpParser\Node\Expr\StaticCall || $node instanceof \MolliePrefix\PhpParser\Node\Expr\Array_) {
            return $this->p($node);
        } else {
            return '(' . $this->p($node) . ')';
        }
    }
    private function hasNodeWithComments(array $nodes)
    {
        foreach ($nodes as $node) {
            if ($node && $node->getAttribute('comments')) {
                return \true;
            }
        }
        return \false;
    }
    private function pMaybeMultiline(array $nodes, $trailingComma = \false)
    {
        if (!$this->hasNodeWithComments($nodes)) {
            return $this->pCommaSeparated($nodes);
        } else {
            return $this->pCommaSeparatedMultiline($nodes, $trailingComma) . "\n";
        }
    }
}
