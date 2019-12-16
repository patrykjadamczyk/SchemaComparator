<?php
//
//function compare($pre_handle, $post_handle, $pre_headers, $log_name): void
//{
//    $log = fopen($log_name, 'w');
//    fwrite($log, "$log_name\n");
//    $line = 1;
//    while ($pre_line = fgetcsv($pre_handle)) {
//        $line++;
//        $post_line = fgetcsv($post_handle);
//        if ($pre_line !== $post_line) {
//            fwrite($log, "Difference on line $line\n");
//            foreach ($pre_line as $key => $cell) {
//                if ($cell !== $post_line[$key]) {
//                    fwrite($log,"Column: $pre_headers[$key], Pre value: $cell, Post value: $post_line[$key]\n");
//                }
//            }
//        }
//    }
//    fclose($log);
//}
//
//$pre_handle = fopen('pre/constraints.csv', 'r');
//$pre_headers = fgetcsv($pre_handle);
//$post_handle = fopen('post/constraints.csv', 'r');
//$post_headers = fgetcsv($post_handle);
//$log_path = 'log/constraints.log';
//compare($pre_handle, $post_handle, $pre_headers, $log_path);
//
//$pre_handle = fopen('pre/columns.csv', 'r');
//$pre_headers = fgetcsv($pre_handle);
//$post_handle = fopen('post/columns.csv', 'r');
//$post_headers = fgetcsv($post_handle);
//$log_path = 'log/columns.log';
//compare($pre_handle, $post_handle, $pre_headers, $log_path);
$pre_handle = fopen('pre/columns.csv', 'r');
$pre_headers = fgetcsv($pre_handle);
$post_handle = fopen('post/columns.csv', 'r');
$post_headers = fgetcsv($post_handle);
$log_path = 'log/constraints.log';
$line = 1;
$investigated_table = '';
//var_dump(memory_get_usage());

$pre_map = [];
while ($pre_line = fgetcsv($pre_handle)) {
    $pre_map[$pre_line[2]]['columns'][$pre_line[3]]['type'] = $pre_line[7];
    $pre_map[$pre_line[2]]['columns'][$pre_line[3]]['nullable'] = $pre_line[6];
    $pre_map[$pre_line[2]]['columns'][$pre_line[3]]['default'] = $pre_line[5];
    $pre_map[$pre_line[2]]['columns'][$pre_line[3]]['extra'] = $pre_line[17];
}

$post_map = [];
while ($pre_line = fgetcsv($post_handle)) {
    $post_map[$pre_line[2]]['columns'][$pre_line[3]]['type'] = $pre_line[7];
    $post_map[$pre_line[2]]['columns'][$pre_line[3]]['nullable'] = $pre_line[6];
    $post_map[$pre_line[2]]['columns'][$pre_line[3]]['default'] = $pre_line[5];
    $post_map[$pre_line[2]]['columns'][$pre_line[3]]['extra'] = $pre_line[17];
}


foreach ($pre_map as $table_name => $table_data) {
    if (!(count($table_data['columns']) === count($post_map[$table_name]['columns']))) {
        echo "Table: $table_name, count of columns does not match.";
    }
    foreach ($table_data['columns'] as $column_name => $column) {
        $post = $post_map[$table_name]['columns'][$column_name];
        if ($post['type'] !== $column['type']) {
            echo "Column types don't match in table: $table_name, column: $column_name, pre value: {$column['type']}, post value: {$post_map[$table_name]['columns'][$column_name]['type']}\n";
        }
        if($post['nullable'] !== $column['nullable']){
            echo "Column nullable doesn't match in table: $table_name, column: $column_name, pre value: {$column['nullable']}, post value: {$post_map[$table_name]['columns'][$column_name]['nullable']}\n";
        }
        if($post['default'] !== $column['default']){
            echo "Column defaults don't match in table: $table_name, column: $column_name, pre value: {$column['default']}, post value: {$post_map[$table_name]['columns'][$column_name]['default']}\n";
        }
        if($post['extra'] !== $column['extra']){
            echo "Column auto increment doesn't match in table: $table_name, column: $column_name, pre value: {$column['extra']}, post value: {$post_map[$table_name]['columns'][$column_name]['extra']}\n";
        }
    }
}

file_put_contents('pre.log', var_export($pre_map, true));
file_put_contents('post.log', var_export($post_map, true));
