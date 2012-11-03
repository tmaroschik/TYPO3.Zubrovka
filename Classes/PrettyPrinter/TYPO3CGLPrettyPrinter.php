<?php
namespace TYPO3\Zubrovka\PrettyPrinter;

/*                                                                        *
 * This script belongs to the FLOW3 framework.                            *
 *                                                                        *
 * It is free software; you can redistribute it and/or modify it under    *
 * the terms of the GNU Lesser General Public License, either version 3   *
 * of the License, or (at your option) any later version.                 *
 *                                                                        *
 * The TYPO3 project - inspiring people to share!                         *
 *                                                                        */

use TYPO3\FLOW3\Annotations as FLOW3;

/**
 * @FLOW3\Scope("prototype")
 */
class TYPO3CGLPrettyPrinter extends \PHPParser_PrettyPrinterAbstract {


	/**
	 * Pretty prints an array of nodes (statements).
	 *
	 * @param \PHPParser_Node[] $nodes Array of nodes
	 *
	 * @return string Pretty printed nodes
	 */
	public function prettyPrint(array $nodes) {
		$code = str_replace(PHP_EOL . $this->noIndentToken, PHP_EOL, $this->pStmts($nodes, FALSE));
		$code = preg_replace("![ \t]+$!m", '', $code);
		return $code;
	}

	// Special nodes

	public function pParam(\PHPParser_Node_Param $node) {
		return ($node->getType() ? (is_string($node->getType()) ? $node->getType() : $this->p($node->getType())) . ' ' : '')
				. ($node->getByRef() ? '&' : '')
				. '$' . $node->getName()
				. ($node->getDefault() ? ' = ' . $this->p($node->getDefault()) : '');
	}

	public function pArg(\PHPParser_Node_Arg $node) {
		return ($node->getByRef() ? '&' : '') . $this->p($node->getValue());
	}

	public function pConst(\PHPParser_Node_Const $node) {
		return $node->getName() . ' = ' . $this->p($node->getValue());
	}

	// Names

	public function pName(\PHPParser_Node_Name $node) {
		return (string)$node;
	}

	public function pName_FullyQualified(\PHPParser_Node_Name_FullyQualified $node) {
		return (string)$node;
	}

	public function pName_Relative(\PHPParser_Node_Name_Relative $node) {
		return (string)$node;
	}

	// Magic Constants

	public function pScalar_ClassConst(\PHPParser_Node_Scalar_ClassConst $node) {
		return '__CLASS__';
	}

	public function pScalar_TraitConst(\PHPParser_Node_Scalar_TraitConst $node) {
		return '__TRAIT__';
	}

	public function pScalar_DirConst(\PHPParser_Node_Scalar_DirConst $node) {
		return '__DIR__';
	}

	public function pScalar_FileConst(\PHPParser_Node_Scalar_FileConst $node) {
		return '__FILE__';
	}

	public function pScalar_FuncConst(\PHPParser_Node_Scalar_FuncConst $node) {
		return '__FUNCTION__';
	}

	public function pScalar_LineConst(\PHPParser_Node_Scalar_LineConst $node) {
		return '__LINE__';
	}

	public function pScalar_MethodConst(\PHPParser_Node_Scalar_MethodConst $node) {
		return '__METHOD__';
	}

	public function pScalar_NSConst(\PHPParser_Node_Scalar_NSConst $node) {
		return '__NAMESPACE__';
	}

	// Scalars

	public function pScalar_String(\PHPParser_Node_Scalar_String $node) {
		return '\'' . $this->pSafe(addcslashes($node->getValue(), '\'\\')) . '\'';
	}

	public function pScalar_Encapsed(\PHPParser_Node_Scalar_Encapsed $node) {
		return '"' . $this->pEncapsList($node->getParts(), '"') . '"';
	}

	public function pScalar_LNumber(\PHPParser_Node_Scalar_LNumber $node) {
		return (string)$node->getValue();
	}

	public function pScalar_DNumber(\PHPParser_Node_Scalar_DNumber $node) {
		$stringValue = (string)$node->getValue();

		// ensure that number is really printed as float
		return ctype_digit($stringValue) ? $stringValue . '.0' : $stringValue;
	}

	// Assignments

	public function pExpr_Assign(\PHPParser_Node_Expr_Assign $node) {
		return $this->p($node->getVar()) . ' = ' . $this->p($node->getExpr());
	}

	public function pExpr_AssignRef(\PHPParser_Node_Expr_AssignRef $node) {
		return $this->p($node->getVar()) . ' =& ' . $this->p($node->getExpr());
	}

	public function pExpr_AssignPlus(\PHPParser_Node_Expr_AssignPlus $node) {
		return $this->p($node->getVar()) . ' += ' . $this->p($node->getExpr());
	}

	public function pExpr_AssignMinus(\PHPParser_Node_Expr_AssignMinus $node) {
		return $this->p($node->getVar()) . ' -= ' . $this->p($node->getExpr());
	}

	public function pExpr_AssignMul(\PHPParser_Node_Expr_AssignMul $node) {
		return $this->p($node->getVar()) . ' *= ' . $this->p($node->getExpr());
	}

	public function pExpr_AssignDiv(\PHPParser_Node_Expr_AssignDiv $node) {
		return $this->p($node->getVar()) . ' /= ' . $this->p($node->getExpr());
	}

	public function pExpr_AssignConcat(\PHPParser_Node_Expr_AssignConcat $node) {
		return $this->p($node->getVar()) . ' .= ' . $this->p($node->getExpr());
	}

	public function pExpr_AssignMod(\PHPParser_Node_Expr_AssignMod $node) {
		return $this->p($node->getVar()) . ' %= ' . $this->p($node->getExpr());
	}

	public function pExpr_AssignBitwiseAnd(\PHPParser_Node_Expr_AssignBitwiseAnd $node) {
		return $this->p($node->getVar()) . ' &= ' . $this->p($node->getExpr());
	}

	public function pExpr_AssignBitwiseOr(\PHPParser_Node_Expr_AssignBitwiseOr $node) {
		return $this->p($node->getVar()) . ' |= ' . $this->p($node->getExpr());
	}

	public function pExpr_AssignBitwiseXor(\PHPParser_Node_Expr_AssignBitwiseXor $node) {
		return $this->p($node->getVar()) . ' ^= ' . $this->p($node->getExpr());
	}

	public function pExpr_AssignShiftLeft(\PHPParser_Node_Expr_AssignShiftLeft $node) {
		return $this->p($node->getVar()) . ' <<= ' . $this->p($node->getExpr());
	}

	public function pExpr_AssignShiftRight(\PHPParser_Node_Expr_AssignShiftRight $node) {
		return $this->p($node->getVar()) . ' >>= ' . $this->p($node->getExpr());
	}

	public function pExpr_AssignList(\PHPParser_Node_Expr_AssignList $node) {
		return $this->pAssignList($node->getVars()) . ' = ' . $this->p($node->getExpr());
	}

	// Binary expressions

	public function pExpr_Plus(\PHPParser_Node_Expr_Plus $node) {
		return $this->p($node->getLeft()) . ' + ' . $this->p($node->getRight());
	}

	public function pExpr_Minus(\PHPParser_Node_Expr_Minus $node) {
		return $this->p($node->getLeft()) . ' - ' . $this->p($node->getRight());
	}

	public function pExpr_Mul(\PHPParser_Node_Expr_Mul $node) {
		return $this->p($node->getLeft()) . ' * ' . $this->p($node->getRight());
	}

	public function pExpr_Div(\PHPParser_Node_Expr_Div $node) {
		return $this->p($node->getLeft()) . ' / ' . $this->p($node->getRight());
	}

	public function pExpr_Concat(\PHPParser_Node_Expr_Concat $node) {
		return $this->p($node->getLeft()) . ' . ' . $this->p($node->getRight());
	}

	public function pExpr_Mod(\PHPParser_Node_Expr_Mod $node) {
		return $this->p($node->getLeft()) . ' % ' . $this->p($node->getRight());
	}

	public function pExpr_BooleanAnd(\PHPParser_Node_Expr_BooleanAnd $node) {
		return $this->p($node->getLeft()) . ' && ' . $this->p($node->getRight());
	}

	public function pExpr_BooleanOr(\PHPParser_Node_Expr_BooleanOr $node) {
		return $this->p($node->getLeft()) . ' || ' . $this->p($node->getRight());
	}

	public function pExpr_BitwiseAnd(\PHPParser_Node_Expr_BitwiseAnd $node) {
		return $this->p($node->getLeft()) . ' & ' . $this->p($node->getRight());
	}

	public function pExpr_BitwiseOr(\PHPParser_Node_Expr_BitwiseOr $node) {
		return $this->p($node->getLeft()) . ' | ' . $this->p($node->getRight());
	}

	public function pExpr_BitwiseXor(\PHPParser_Node_Expr_BitwiseXor $node) {
		return $this->p($node->getLeft()) . ' ^ ' . $this->p($node->getRight());
	}

	public function pExpr_ShiftLeft(\PHPParser_Node_Expr_ShiftLeft $node) {
		return $this->p($node->getLeft()) . ' << ' . $this->p($node->getRight());
	}

	public function pExpr_ShiftRight(\PHPParser_Node_Expr_ShiftRight $node) {
		return $this->p($node->getLeft()) . ' >> ' . $this->p($node->getRight());
	}

	public function pExpr_LogicalAnd(\PHPParser_Node_Expr_LogicalAnd $node) {
		return $this->p($node->getLeft()) . ' and ' . $this->p($node->getRight());
	}

	public function pExpr_LogicalOr(\PHPParser_Node_Expr_LogicalOr $node) {
		return $this->p($node->getLeft()) . ' or ' . $this->p($node->getRight());
	}

	public function pExpr_LogicalXor(\PHPParser_Node_Expr_LogicalXor $node) {
		return $this->p($node->getLeft()) . ' xor ' . $this->p($node->getRight());
	}

	public function pExpr_Equal(\PHPParser_Node_Expr_Equal $node) {
		return $this->p($node->getLeft()) . ' == ' . $this->p($node->getRight());
	}

	public function pExpr_NotEqual(\PHPParser_Node_Expr_NotEqual $node) {
		return $this->p($node->getLeft()) . ' != ' . $this->p($node->getRight());
	}

	public function pExpr_Identical(\PHPParser_Node_Expr_Identical $node) {
		return $this->p($node->getLeft()) . ' === ' . $this->p($node->getRight());
	}

	public function pExpr_NotIdentical(\PHPParser_Node_Expr_NotIdentical $node) {
		return $this->p($node->getLeft()) . ' !== ' . $this->p($node->getRight());
	}

	public function pExpr_Greater(\PHPParser_Node_Expr_Greater $node) {
		return $this->p($node->getLeft()) . ' > ' . $this->p($node->getRight());
	}

	public function pExpr_GreaterOrEqual(\PHPParser_Node_Expr_GreaterOrEqual $node) {
		return $this->p($node->getLeft()) . ' >= ' . $this->p($node->getRight());
	}

	public function pExpr_Smaller(\PHPParser_Node_Expr_Smaller $node) {
		return $this->p($node->getLeft()) . ' < ' . $this->p($node->getRight());
	}

	public function pExpr_SmallerOrEqual(\PHPParser_Node_Expr_SmallerOrEqual $node) {
		return $this->p($node->getLeft()) . ' <= ' . $this->p($node->getRight());
	}

	public function pExpr_Instanceof(\PHPParser_Node_Expr_Instanceof $node) {
		return $this->p($node->getExpr()) . ' instanceof ' . $this->p($node->getClass());
	}

	// Unary expressions

	public function pExpr_BooleanNot(\PHPParser_Node_Expr_BooleanNot $node) {
		return '!' . $this->p($node->getExpr());
	}

	public function pExpr_BitwiseNot(\PHPParser_Node_Expr_BitwiseNot $node) {
		return '~' . $this->p($node->getExpr());
	}

	public function pExpr_UnaryMinus(\PHPParser_Node_Expr_UnaryMinus $node) {
		return '-' . $this->p($node->getExpr());
	}

	public function pExpr_UnaryPlus(\PHPParser_Node_Expr_UnaryPlus $node) {
		return '+' . $this->p($node->getExpr());
	}

	public function pExpr_PreInc(\PHPParser_Node_Expr_PreInc $node) {
		return '++' . $this->p($node->getVar());
	}

	public function pExpr_PreDec(\PHPParser_Node_Expr_PreDec $node) {
		return '--' . $this->p($node->getVar());
	}

	public function pExpr_PostInc(\PHPParser_Node_Expr_PostInc $node) {
		return $this->p($node->getVar()) . '++';
	}

	public function pExpr_PostDec(\PHPParser_Node_Expr_PostDec $node) {
		return $this->p($node->getVar()) . '--';
	}

	public function pExpr_ErrorSuppress(\PHPParser_Node_Expr_ErrorSuppress $node) {
		return '@' . $this->p($node->getExpr());
	}

	// Casts

	public function pExpr_Cast_Int(\PHPParser_Node_Expr_Cast_Int $node) {
		return '(int) ' . $this->p($node->getExpr());
	}

	public function pExpr_Cast_Double(\PHPParser_Node_Expr_Cast_Double $node) {
		return '(double) ' . $this->p($node->getExpr());
	}

	public function pExpr_Cast_String(\PHPParser_Node_Expr_Cast_String $node) {
		return '(string) ' . $this->p($node->getExpr());
	}

	public function pExpr_Cast_Array(\PHPParser_Node_Expr_Cast_Array $node) {
		return '(array) ' . $this->p($node->getExpr());
	}

	public function pExpr_Cast_Object(\PHPParser_Node_Expr_Cast_Object $node) {
		return '(object) ' . $this->p($node->getExpr());
	}

	public function pExpr_Cast_Bool(\PHPParser_Node_Expr_Cast_Bool $node) {
		return '(bool) ' . $this->p($node->getExpr());
	}

	public function pExpr_Cast_Unset(\PHPParser_Node_Expr_Cast_Unset $node) {
		return '(unset) ' . $this->p($node->getExpr());
	}

	// Function calls and similar constructs

	public function pExpr_FuncCall(\PHPParser_Node_Expr_FuncCall $node) {
		return $this->p($node->getName()) . '(' . $this->pCommaSeparated($node->getArgs()) . ')';
	}

	public function pExpr_MethodCall(\PHPParser_Node_Expr_MethodCall $node) {
		return $this->pVarOrNewExpr($node->getVar()) . '->' . $this->pObjectProperty($node->getName())
				. '(' . $this->pCommaSeparated($node->getArgs()) . ')';
	}

	public function pExpr_StaticCall(\PHPParser_Node_Expr_StaticCall $node) {
		return $this->p($node->getClass()) . '::'
				. ($node->getName() instanceof \PHPParser_Node_Expr
						? ($node->getName() instanceof \PHPParser_Node_Expr_Variable
								|| $node->getName() instanceof \PHPParser_Node_Expr_ArrayDimFetch
								? $this->p($node->getName())
								: '{' . $this->p($node->getName()) . '}')
						: $node->getName())
				. '(' . $this->pCommaSeparated($node->getArgs()) . ')';
	}

	public function pExpr_Empty(\PHPParser_Node_Expr_Empty $node) {
		return 'empty(' . $this->p($node->getVar()) . ')';
	}

	public function pExpr_Isset(\PHPParser_Node_Expr_Isset $node) {
		return 'isset(' . $this->pCommaSeparated($node->getVars()) . ')';
	}

	public function pExpr_Print(\PHPParser_Node_Expr_Print $node) {
		return 'print ' . $this->p($node->getExpr());
	}

	public function pExpr_Eval(\PHPParser_Node_Expr_Eval $node) {
		return 'eval(' . $this->p($node->getExpr()) . ')';
	}

	public function pExpr_Include(\PHPParser_Node_Expr_Include $node) {
		static $map = array(
			\PHPParser_Node_Expr_Include::TYPE_INCLUDE => 'include',
			\PHPParser_Node_Expr_Include::TYPE_INCLUDE_ONCE => 'include_once',
			\PHPParser_Node_Expr_Include::TYPE_REQUIRE => 'require',
			\PHPParser_Node_Expr_Include::TYPE_REQUIRE_ONCE => 'require_once',
		);

		return $map[$node->getType()] . ' ' . $this->p($node->getExpr());
	}

	// Other

	public function pExpr_Variable(\PHPParser_Node_Expr_Variable $node) {
		if ($node->getName() instanceof \PHPParser_Node_Expr) {
			return '${' . $this->p($node->getName()) . '}';
		} else {
			return '$' . $node->getName();
		}
	}

	public function pExpr_Array(\PHPParser_Node_Expr_Array $node) {
		return 'array(' . $this->pCommaSeparated($node->getItems(), $node->itemsHaveLineBreaks()) . ')';
	}

	public function pExpr_ArrayItem(\PHPParser_Node_Expr_ArrayItem $node) {
		return (NULL !== $node->getKey() ? $this->p($node->getKey()) . ' => ' : '')
				. ($node->getByRef() ? '&' : '') . $this->p($node->getValue());
	}

	public function pExpr_ArrayDimFetch(\PHPParser_Node_Expr_ArrayDimFetch $node) {
		return $this->pVarOrNewExpr($node->getVar())
				. '[' . (NULL !== $node->getDim() ? $this->p($node->getDim()) : '') . ']';
	}

	public function pExpr_ConstFetch(\PHPParser_Node_Expr_ConstFetch $node) {
		return $this->p($node->getName());
	}

	public function pExpr_ClassConstFetch(\PHPParser_Node_Expr_ClassConstFetch $node) {
		return $this->p($node->getClass()) . '::' . $node->getName();
	}

	public function pExpr_PropertyFetch(\PHPParser_Node_Expr_PropertyFetch $node) {
		return $this->pVarOrNewExpr($node->getVar()) . '->' . $this->pObjectProperty($node->getName());
	}

	public function pExpr_StaticPropertyFetch(\PHPParser_Node_Expr_StaticPropertyFetch $node) {
		return $this->p($node->getClass()) . '::$' . $this->pObjectProperty($node->getName());
	}

	public function pExpr_ShellExec(\PHPParser_Node_Expr_ShellExec $node) {
		return '`' . $this->pEncapsList($node->getParts(), '`') . '`';
	}

	public function pExpr_Closure(\PHPParser_Node_Expr_Closure $node) {
		$uses = $node->getUses();
		return ($node->getStatic() ? 'static ' : '')
				. 'function ' . ($node->getByRef() ? '&' : '')
				. '(' . $this->pCommaSeparated($node->getParams()) . ')'
				. (!empty($uses) ? ' use(' . $this->pCommaSeparated($uses) . ')' : '')
				. ' {' . PHP_EOL . $this->pStmts($node->getStmts()) . PHP_EOL . '}';
	}

	public function pExpr_ClosureUse(\PHPParser_Node_Expr_ClosureUse $node) {
		return ($node->getByRef() ? '&' : '') . '$' . $node->getVar();
	}

	public function pExpr_New(\PHPParser_Node_Expr_New $node) {
		return 'new ' . $this->p($node->getClass()) . '(' . $this->pCommaSeparated($node->getArgs()) . ')';
	}

	public function pExpr_Clone(\PHPParser_Node_Expr_Clone $node) {
		return 'clone ' . $this->p($node->getExpr());
	}

	public function pExpr_Ternary(\PHPParser_Node_Expr_Ternary $node) {
		return $this->p($node->getCond()) . ' ?'
				. (NULL !== $node->getIf() ? ' ' . $this->p($node->getIf()) . ' ' : '')
				. ': ' . $this->p($node->getElse());
	}

	public function pExpr_Exit(\PHPParser_Node_Expr_Exit $node) {
		return 'die' . (NULL !== $node->getExpr() ? '(' . $this->p($node->getExpr()) . ')' : '');
	}

	// Declarations

	public function pStmt_Namespace(\PHPParser_Node_Stmt_Namespace $node) {
		return 'namespace' . (NULL !== $node->getName() ? ' ' . $this->p($node->getName()) : '')
				. ';' . PHP_EOL . PHP_EOL . $this->pStmts($node->getStmts(), FALSE) . PHP_EOL . '';
	}

	public function pStmt_Use(\PHPParser_Node_Stmt_Use $node) {
		return 'use ' . $this->pCommaSeparated($node->getUses()) . ';';
	}

	public function pStmt_UseUse(\PHPParser_Node_Stmt_UseUse $node) {
		return $this->p($node->getName())
				. ($node->getName()->getLast() !== $node->getAlias() ? ' as ' . $node->getAlias() : '');
	}

	public function pStmt_Interface(\PHPParser_Node_Stmt_Interface $node) {
		$extends = $node->getExtends();
		return 'interface ' . $node->getName()
				. (!empty($extends) ? ' extends ' . $this->pCommaSeparated($extends) : '')
				. PHP_EOL . '{' . PHP_EOL . $this->pStmts($node->getStmts()) . PHP_EOL . '}';
	}

	public function pStmt_Class(\PHPParser_Node_Stmt_Class $node) {
		$implements = $node->getImplements();
		return $this->pModifiers($node->getType())
				. 'class ' . $node->getName()
				. (NULL !== $node->getExtends() ? ' extends ' . $this->p($node->getExtends()) : '')
				. (!empty($implements) ? ' implements ' . $this->pCommaSeparated($implements) : '')
				. ' {' . PHP_EOL . PHP_EOL . $this->pStmts($node->getStmts()) . PHP_EOL . '}' . PHP_EOL;
	}

	public function pStmt_Trait(\PHPParser_Node_Stmt_Trait $node) {
		return 'trait ' . $node->getName()
				. PHP_EOL . '{' . PHP_EOL . $this->pStmts($node->getStmts()) . PHP_EOL . '}';
	}

	public function pStmt_TraitUse(\PHPParser_Node_Stmt_TraitUse $node) {
		$adaptions = $node->getAdaptations();
		return 'use ' . $this->pCommaSeparated($node->getTraits())
				. (empty($adaptions)
						? ';'
						: ' {' . PHP_EOL . $this->pStmts($adaptions) . PHP_EOL . '}');
	}

	public function pStmt_TraitUseAdaptation_Precedence(\PHPParser_Node_Stmt_TraitUseAdaptation_Precedence $node) {
		return $this->p($node->getTrait()) . '::' . $node->getMethod()
				. ' insteadof ' . $this->pCommaSeparated($node->getInsteadof()) . ';';
	}

	public function pStmt_TraitUseAdaptation_Alias(\PHPParser_Node_Stmt_TraitUseAdaptation_Alias $node) {
		return (NULL !== $node->getTrait() ? $this->p($node->getTrait()) . '::' : '')
				. $node->getMethod() . ' as'
				. (NULL !== $node->getNewModifier() ? ' ' . $this->pModifiers($node->getNewModifier()) : '')
				. (NULL !== $node->getNewName() ? ' ' . $node->getNewName() : '')
				. ';';
	}

	public function pStmt_Property(\PHPParser_Node_Stmt_Property $node) {
		$message  = '';
		if ($node->getType() === 0) {
			$node->setType(\PHPParser_Node_Stmt_Class::MODIFIER_PUBLIC);
			$message = ' // TODO Define visibility since automatic converted from var';
		}
		return $this->pModifiers($node->getType()) . $this->pCommaSeparated($node->getProps()) . ';' . $message . PHP_EOL;
	}

	public function pStmt_PropertyProperty(\PHPParser_Node_Stmt_PropertyProperty $node) {
		return '$' . $node->getName()
				. (NULL !== $node->getDefault() ? ' = ' . $this->p($node->getDefault()) : '');
	}

	public function pStmt_ClassMethod(\PHPParser_Node_Stmt_ClassMethod $node) {
		return $this->pModifiers($node->getType())
				. 'function ' . ($node->getByRef() ? '&' : '') . $node->getName()
				. '(' . $this->pCommaSeparated($node->getParams()) . ')'
				. (NULL !== $node->getStmts()
						? ' {' . PHP_EOL . $this->pStmts($node->getStmts(), TRUE, TRUE) . PHP_EOL . '}'
						: ';') . PHP_EOL;
	}

	public function pStmt_ClassConst(\PHPParser_Node_Stmt_ClassConst $node) {
		return 'const ' . $this->pCommaSeparated($node->getConsts()) . ';';
	}

	public function pStmt_Function(\PHPParser_Node_Stmt_Function $node) {
		return 'function ' . ($node->getByRef() ? '&' : '') . $node->getName()
				. '(' . $this->pCommaSeparated($node->getParams()) . ')'
				. PHP_EOL . '{' . PHP_EOL . $this->pStmts($node->getStmts(), TRUE, TRUE) . PHP_EOL . '}';
	}

	public function pStmt_Const(\PHPParser_Node_Stmt_Const $node) {
		return 'const ' . $this->pCommaSeparated($node->getConsts()) . ';';
	}

	public function pStmt_Declare(\PHPParser_Node_Stmt_Declare $node) {
		return 'declare (' . $this->pCommaSeparated($node->getDeclares()) . ') {'
				. PHP_EOL . $this->pStmts($node->getStmts()) . PHP_EOL . '}';
	}

	public function pStmt_DeclareDeclare(\PHPParser_Node_Stmt_DeclareDeclare $node) {
		return $node->getKey() . ' = ' . $this->p($node->getValue());
	}

	// Control flow

	public function pStmt_If(\PHPParser_Node_Stmt_If $node) {
		return 'if (' . $this->p($node->getCond()) . ') {'
				. PHP_EOL . $this->pStmts($node->getStmts(), TRUE, TRUE) . PHP_EOL . '}'
				. $this->pImplode($node->getElseifs())
				. (NULL !== $node->getElse() ? $this->p($node->getElse()) : '');
	}

	public function pStmt_Elseif(\PHPParser_Node_Stmt_Elseif $node) {
		return ' elseif (' . $this->p($node->getCond()) . ') {'
				. PHP_EOL . $this->pStmts($node->getStmts(), TRUE, TRUE) . PHP_EOL . '}';
	}

	public function pStmt_Else(\PHPParser_Node_Stmt_Else $node) {
		return ' else {' . PHP_EOL . $this->pStmts($node->getStmts(), TRUE, TRUE) . PHP_EOL . '}';
	}

	public function pStmt_For(\PHPParser_Node_Stmt_For $node) {
		$cond = $node->getCond();
		$loop = $node->getLoop();
		return 'for ('
				. $this->pCommaSeparated($node->getInit()) . ';' . (!empty($cond) ? ' ' : '')
				. $this->pCommaSeparated($node->getCond()) . ';' . (!empty($loop) ? ' ' : '')
				. $this->pCommaSeparated($node->getLoop())
				. ') {' . PHP_EOL . $this->pStmts($node->getStmts(), TRUE, TRUE) . PHP_EOL . '}';
	}

	public function pStmt_Foreach(\PHPParser_Node_Stmt_Foreach $node) {
		return 'foreach (' . $this->p($node->getExpr()) . ' as '
				. (NULL !== $node->getKeyVar() ? $this->p($node->getKeyVar()) . ' => ' : '')
				. ($node->getByRef() ? '&' : '') . $this->p($node->getValueVar()) . ') {'
				. PHP_EOL . $this->pStmts($node->getStmts(), TRUE, TRUE) . PHP_EOL . '}';
	}

	public function pStmt_While(\PHPParser_Node_Stmt_While $node) {
		return 'while (' . $this->p($node->getCond()) . ') {'
				. PHP_EOL . $this->pStmts($node->getStmts(), TRUE, TRUE) . PHP_EOL . '}';
	}

	public function pStmt_Do(\PHPParser_Node_Stmt_Do $node) {
		return 'do {' . PHP_EOL . $this->pStmts($node->getStmts(), TRUE, TRUE) . PHP_EOL
				. '} while (' . $this->p($node->getCond()) . ');';
	}

	public function pStmt_Switch(\PHPParser_Node_Stmt_Switch $node) {
		return 'switch (' . $this->p($node->getCond()) . ') {'
				. PHP_EOL . $this->pImplode($node->getCases()) . '}';
	}

	public function pStmt_TryCatch(\PHPParser_Node_Stmt_TryCatch $node) {
		return 'try {' . PHP_EOL . $this->pStmts($node->getStmts(), TRUE, TRUE) . PHP_EOL . '}'
				. $this->pImplode($node->getCatches());
	}

	public function pStmt_Catch(\PHPParser_Node_Stmt_Catch $node) {
		return ' catch (' . $this->p($node->getType()) . ' $' . $node->getVar() . ') {'
				. PHP_EOL . $this->pStmts($node->getStmts(), TRUE, TRUE) . PHP_EOL . '}';
	}

	public function pStmt_Case(\PHPParser_Node_Stmt_Case $node) {
		return (NULL !== $node->getCond() ? 'case ' . $this->p($node->getCond()) : 'default') . ':'
				. PHP_EOL . $this->pStmts($node->getStmts(), TRUE, TRUE) . PHP_EOL;
	}

	public function pStmt_Break(\PHPParser_Node_Stmt_Break $node) {
		return 'break' . ($node->getNum() !== NULL ? ' ' . $this->p($node->getNum()) : '') . ';';
	}

	public function pStmt_Continue(\PHPParser_Node_Stmt_Continue $node) {
		return 'continue' . ($node->getNum() !== NULL ? ' ' . $this->p($node->getNum()) : '') . ';';
	}

	public function pStmt_Return(\PHPParser_Node_Stmt_Return $node) {
		return 'return' . (NULL !== $node->getExpr() ? ' ' . $this->p($node->getExpr()) : '') . ';';
	}

	public function pStmt_Throw(\PHPParser_Node_Stmt_Throw $node) {
		return 'throw ' . $this->p($node->getExpr()) . ';';
	}

	public function pStmt_Label(\PHPParser_Node_Stmt_Label $node) {
		return $node->getName() . ':';
	}

	public function pStmt_Goto(\PHPParser_Node_Stmt_Goto $node) {
		return 'goto ' . $node->getName() . ';';
	}

	// Other

	public function pStmt_Echo(\PHPParser_Node_Stmt_Echo $node) {
		return 'echo ' . $this->pCommaSeparated($node->getExprs()) . ';';
	}

	public function pStmt_Static(\PHPParser_Node_Stmt_Static $node) {
		return 'static ' . $this->pCommaSeparated($node->getVars()) . ';';
	}

	public function pStmt_Global(\PHPParser_Node_Stmt_Global $node) {
		return 'global ' . $this->pCommaSeparated($node->getVars()) . ';';
	}

	public function pStmt_StaticVar(\PHPParser_Node_Stmt_StaticVar $node) {
		return '$' . $node->getName()
				. (NULL !== $node->getDefault() ? ' = ' . $this->p($node->getDefault()) : '');
	}

	public function pStmt_Unset(\PHPParser_Node_Stmt_Unset $node) {
		return 'unset(' . $this->pCommaSeparated($node->getVars()) . ');';
	}

	public function pStmt_InlineHTML(\PHPParser_Node_Stmt_InlineHTML $node) {
		return '?>' . $this->pSafe(
			(PHP_EOL === substr($node->getValue(), 0, 1) || "\r" === substr($node->getValue(), 0, 1) ? PHP_EOL : '')
					. $node->getValue()
		) . '<?php ';
	}

	public function pStmt_HaltCompiler(\PHPParser_Node_Stmt_HaltCompiler $node) {
		return '__halt_compiler();' . $node->getRemaining();
	}

	// Helpers

	public function pObjectProperty($node) {
		if ($node instanceof \PHPParser_Node_Expr) {
			return '{' . $this->p($node) . '}';
		} else {
			return $node;
		}
	}

	public function pModifiers($modifiers) {
		return ($modifiers & \PHPParser_Node_Stmt_Class::MODIFIER_STATIC ? 'static ' : '')
				. ($modifiers & \PHPParser_Node_Stmt_Class::MODIFIER_ABSTRACT ? 'abstract ' : '')
				. ($modifiers & \PHPParser_Node_Stmt_Class::MODIFIER_FINAL ? 'final ' : '')
				. ($modifiers & \PHPParser_Node_Stmt_Class::MODIFIER_PUBLIC ? 'public ' : '')
				. ($modifiers & \PHPParser_Node_Stmt_Class::MODIFIER_PROTECTED ? 'protected ' : '')
				. ($modifiers & \PHPParser_Node_Stmt_Class::MODIFIER_PRIVATE ? 'private ' : '');
	}

	public function pEncapsList(array $encapsList, $quote) {
		$return = '';
		foreach ($encapsList as $element) {
			if (is_string($element)) {
				$return .= addcslashes($element, PHP_EOL . "\t\f\v$" . $quote . "\\");
			} else {
				$return .= '{' . $this->p($element) . '}';
			}
		}

		return $return;
	}

	public function pAssignList(array $elements) {
		$pAssignList = array();
		foreach ($elements as $element) {
			if (NULL === $element) {
				$pAssignList[] = '';
			} elseif (is_array($element)) {
				$pAssignList[] = $this->pAssignList($element);
			} else {
				$pAssignList[] = $this->p($element);
			}
		}

		return 'list(' . implode(', ', $pAssignList) . ')';
	}

	public function pVarOrNewExpr(\PHPParser_Node $node) {
		if ($node instanceof \PHPParser_Node_Expr_New) {
			return '(' . $this->p($node) . ')';
		} else {
			return $this->p($node);
		}
	}

	/**
	 * Pretty prints an array of nodes (statements) and indents them optionally.
	 *
	 * @param \PHPParser_Node[] $nodes Array of nodes
	 * @param bool $indent Whether to indent the printed nodes
	 *
	 * @return string Pretty printed statements
	 */
	protected function pStmts(array $nodes, $indent = TRUE, $singleLineCommentAllowed = FALSE) {
		$pNodes = array();
		$nodeKey = 0;
		foreach ($nodes as $node) {
			$ignorableValue = $this->pIgnorable($node->getIgnorables() ? : array(), $singleLineCommentAllowed);
			if (!empty($ignorableValue)) {
				$pNodes[] = $ignorableValue;
				$nodeKey++;
			}
			$value = $this->p($node) . ($node instanceof \PHPParser_Node_Expr ? ';' : '');
			if (!empty($value)) {
				$pNodes[] = $value;
			}
		}
		if ($indent) {
			return "\t" . preg_replace(
				'~\n(?!$|' . $this->noIndentToken . ')~',
					PHP_EOL . "\t",
				implode(PHP_EOL, $pNodes)
			);
		} else {
			return implode(PHP_EOL, $pNodes);
		}
	}

	/**
	 * @param array $ignorables * @param bool $singleLineCommentAllowed
	 * @return string
	 */
	protected function pIgnorable(array $ignorables, $singleLineCommentAllowed = FALSE) {
		$pNodes = array();
		if (NULL !== $ignorables && !empty($ignorables)) {
			foreach ($ignorables as $ignorable) {
				switch ($ignorable) {
					case $ignorable instanceof \PHPParser_Node_Ignorable_Comment:
						$value = trim($ignorable->getValue());
						$value = preg_replace('~^\s*(.*)\s*$~m', '$1', $value);
						$value = preg_replace('~^\*~m', ' *', $value);
						$value = preg_replace('~^\s*\*\/\s*$~', '*/', $value);
						$pNodes[] = preg_replace('~^\s+\/\*+~m', '/*', $value);
						break;
					case $ignorable instanceof \PHPParser_Node_Ignorable_DocComment:
						$pNodes[] = $ignorable->toString($singleLineCommentAllowed);
						break;
				}
			}
		}
		return implode(PHP_EOL, $pNodes);
	}

	/**
	 * Pretty prints a node.
	 *
	 * @param \PHPParser_Node $node Node to be pretty printed
	 *
	 * @return string Pretty printed node
	 */
	protected function p(\PHPParser_Node $node) {
		$type = $node->getNodeType();

		if (isset($this->precedanceMap[$type])) {
			$precedence = $this->precedanceMap[$type];

			if ($precedence >= $this->precedenceStack[$this->precedenceStackPos]) {
				$this->precedenceStack[++$this->precedenceStackPos] = $precedence;
				$return = '(' . $this->{'p' . $type}($node) . ')';
				--$this->precedenceStackPos;
			} else {
				$this->precedenceStack[++$this->precedenceStackPos] = $precedence;
				$return = $this->{'p' . $type}($node);
				--$this->precedenceStackPos;
			}

			return $return;
		} else {
			return $this->{'p' . $type}($node);
		}
	}

	/**
	 * Pretty prints an array of nodes and implodes the printed values.
	 *
	 * @param \PHPParser_Node[] $nodes Array of Nodes to be printed
	 * @param string $glue Character to implode with
	 *
	 * @return string Imploded pretty printed nodes
	 */
	protected function pImplode(array $nodes, $glue = '') {
		$pNodes = array();
		foreach ($nodes as $node) {
			$pNodes[] = $this->p($node);
		}

		return implode($glue, $pNodes);
	}

	/**
	 * Pretty prints an array of nodes and implodes the printed values with commas.
	 *
	 * @param PHPParser_Node[] $nodes Array of Nodes to be printed
	 *
	 * @return string Comma separated pretty printed nodes
	 */
	protected function pCommaSeparated(array $nodes, $indent = FALSE) {
		if ($indent) {
			$pNodes = array();
			foreach ($nodes as $node) {
				$ignorableValue = $this->pIgnorable($node->getIgnorables() ?: array());
				$pNodes[] = (!empty($ignorableValue) ? $ignorableValue . PHP_EOL : '') . $this->p($node);
			}

			return PHP_EOL . "\t" . preg_replace(
				'~\n(?!$|' . $this->noIndentToken . ')~',
					PHP_EOL . "\t",
					implode(',' . PHP_EOL, $pNodes) . PHP_EOL
			);
		} else {
			return $this->pImplode($nodes, ', ');
		}
	}

	public function pBlankNode($node) {
		return '';
	}

}