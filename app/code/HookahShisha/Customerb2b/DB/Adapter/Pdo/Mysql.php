<?php

namespace HookahShisha\Customerb2b\DB\Adapter\Pdo;

class Mysql extends \Magento\Framework\DB\Adapter\Pdo\Mysql
{

    /**
     * Prepare value for save in column
     *
     * Return converted to column data type value
     *
     * @param array $column the column describe array
     * @param mixed $value
     * @return mixed
     *
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    public function prepareColumnValue(array $column, $value)
    {
        if ($value instanceof \Zend_Db_Expr) {
            return $value;
        }
        if ($value instanceof Parameter) {
            return $value;
        }

        // return original value if invalid column describe data
        if (!isset($column['DATA_TYPE'])) {
            return $value;
        }

        // return null
        if ($value === null && $column['NULLABLE']) {
            return null;
        }

        switch ($column['DATA_TYPE']) {
            case 'smallint':
            case 'int':
                $value = (int) $value;
                break;
            case 'bigint':
                if (!is_integer($value)) {
                    $value = sprintf('%.0f', (float) $value);
                }
                break;

            case 'decimal':
                $precision = 10;
                $scale = 0;
                if (isset($column['SCALE'])) {
                    $scale = $column['SCALE'];
                }
                if (isset($column['PRECISION'])) {
                    $precision = $column['PRECISION'];
                }
                $format = sprintf('%%%d.%dF', $precision - $scale, $scale);
                $value = (float) sprintf($format, $value);
                break;

            case 'float':
                $value = (float) sprintf('%F', $value);
                break;

            case 'date':
                $value = $this->formatDate($value, false);
                break;
            case 'datetime':
            case 'timestamp':
                $value = $this->formatDate($value);
                break;

            case 'varchar':
            case 'mediumtext':
            case 'text':
            case 'longtext':
                if (!is_array($value)) {
                    $value = (string) $value;
                } else {
                    $value = '';
                }

                if ($column['NULLABLE'] && $value == '') {
                    $value = null;
                }

                break;

            case 'varbinary':
            case 'mediumblob':
            case 'blob':
            case 'longblob':
                // No special processing for MySQL is needed
                break;
            default:
                break;
        }

        return $value;
    }
}
