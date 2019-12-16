<?php

class SchemaComparator
{
    private $pre_columns_handle;
    private $post_columns_handle;
    private $pre_constraints_handle;
    private $post_constraints_handle;
    private $post_columns_map;
    private $pre_columns_map;
    private $columns_log;
    private $constraints_log;
    private $pre_constraints_map;
    private $post_constraints_map;


    public function __construct
    (
        string $pre_columns = 'pre/columns.csv',
        string $post_columns = 'post/columns.csv',
        string $pre_constraints = 'pre/constraints.csv',
        string $post_constraints = 'post/constraints.csv'
    )
    {
        $this->pre_columns_handle = fopen($pre_columns, 'r');
        $this->post_columns_handle = fopen($post_columns, 'r');
        $this->pre_constraints_handle = fopen($pre_constraints, 'r');
        $this->post_constraints_handle = fopen($post_constraints, 'r');
        $this->columns_log = fopen('log/columns.log', 'w');
        $this->constraints_log = fopen('log/constraints.log', 'w');
        $this->setMaps();
    }

    private function setMaps(): SchemaComparator
    {
        $this->pre_columns_map = $this->getColumnMap($this->pre_columns_handle);
        $this->post_columns_map = $this->getColumnMap($this->post_columns_handle);
        $this->pre_constraints_map = $this->getConstraintMap($this->pre_constraints_handle);
        $this->post_constraints_map = $this->getConstraintMap($this->post_constraints_handle);
        return $this;
    }

    private function getColumnMap($file_handle): array
    {
        $map = [];
        $headers = fgetcsv($file_handle);
        while ($line = fgetcsv($file_handle)) {
            $line = array_combine($headers, $line);
            $map[$line['TABLE_NAME']][$line['COLUMN_NAME']]['type'] = $line['DATA_TYPE'];
            $map[$line['TABLE_NAME']][$line['COLUMN_NAME']]['nullable'] = $line['IS_NULLABLE'];
            $map[$line['TABLE_NAME']][$line['COLUMN_NAME']]['default'] = $line['COLUMN_DEFAULT'];
            $map[$line['TABLE_NAME']][$line['COLUMN_NAME']]['extra'] = $line['EXTRA'];
        }
        return $map;
    }

    private function getConstraintMap($file_handle): array
    {
        $headers = fgetcsv($file_handle);
        $map = [];
        while ($line = fgetcsv($file_handle)) {
            $line = array_combine($headers, $line);
            $map[$line['TABLE_NAME']][$line['CONSTRAINT_NAME']] = $line['CONSTRAINT_TYPE'];
        }
        return $map;
    }

    public function compare(): void
    {
        $this->compareColumns();
        $this->compareConstraints();
    }

    private function compareConstraints(): void
    {
        foreach ($this->pre_constraints_map as $table_name => $constraints) {
            foreach ($constraints as $constraint_name => $constraint_type) {
                $post_constraint = isset($this->post_constraints_map[$table_name][$constraint_name]) ?
                    $this->post_constraints_map[$table_name][$constraint_name] : null;
                if (!$post_constraint) {
                    fwrite(
                        $this->constraints_log,
                        "UNABLE TO FIND CONSTRAINT with name $constraint_name in table $table_name after the update\n"
                    );
                } elseif ($constraint_type !== $this->post_constraints_map[$table_name][$constraint_name]) {
                    fwrite(
                        $this->constraints_log,
                        "CONSTRAINT VALUE CHANGED for constraint with name $constraint_name in table $table_name, before the update: $constraint_type, after the update: $post_constraint\n"
                    );
                }
            }
        }
    }

    private function compareColumns(): void
    {
        foreach ($this->pre_columns_map as $table_name => $columns) {
            foreach ($columns as $column_name => $column) {
                $post_column = isset($this->post_columns_map[$table_name][$column_name]) ? $this->post_columns_map[$table_name][$column_name] : null;
                if (!$post_column) {
                    fwrite(
                        $this->columns_log,
                        "DELETED COLUMN in table $table_name, column: $column_name\n"
                    );
                    continue;
                }
                if ($post_column['type'] !== $column['type']) {
                    fwrite(
                        $this->columns_log,
                        "COLUMN_TYPE mismatch in table $table_name for column $column_name, pre-update value: {$column['type']}, post-update value: {$post_column['type']}\n"
                    );
                }
                if ($post_column['nullable'] !== $column['nullable']) {
                    fwrite(
                        $this->columns_log,
                        "NULLABLE has changed in table $table_name for column: $column_name, pre-update value: {$column['nullable']}, post-update value: {$post_column['nullable']}\n"
                    );
                }
                if ($post_column['default'] !== $column['default']) {
                    fwrite(
                        $this->columns_log,
                        "COLUMN_DEFAULT has changed in table $table_name for column: $column_name, pre-update value: {$column['default']}, post-update value: {$post_column['default']}\n"
                    );
                }
                if ($post_column['extra'] !== $column['extra']) {
                    fwrite(
                        $this->columns_log,
                        "EXTRA has changed in table $table_name for column $column_name, pre-update value: {$column['extra']}, post-update value: {$post_column['extra']}\n"
                    );
                }
            }
        }
    }
}

$x = (new SchemaComparator())->compare();