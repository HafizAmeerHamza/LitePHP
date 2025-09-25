<?php

/**
 * Dump and die - Laravel-like debugging function
 */
function dd(...$vars)
{
    // Set content type to HTML for better formatting
    if (!headers_sent()) {
        header('Content-Type: text/html; charset=utf-8');
    }
    
    echo '<style>
        .dd-container {
            background: #1e1e1e;
            color: #f8f8f2;
            font-family: "Fira Code", "Monaco", "Consolas", monospace;
            font-size: 14px;
            padding: 20px;
            margin: 20px;
            border-radius: 8px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            max-height: 70vh;
            overflow: auto;
        }
        .dd-title {
            color: #ff6b6b;
            font-weight: bold;
            margin-bottom: 15px;
            font-size: 16px;
            border-bottom: 2px solid #333;
            padding-bottom: 10px;
        }
        .dd-location {
            color: #74c0fc;
            font-size: 12px;
            margin-bottom: 15px;
        }
        .dd-var {
            margin-bottom: 20px;
            border: 1px solid #333;
            border-radius: 4px;
            padding: 15px;
            background: #2d2d2d;
        }
        .dd-type {
            color: #ffd43b;
            font-weight: bold;
            font-size: 12px;
            margin-bottom: 8px;
        }
        .dd-content {
            white-space: pre-wrap;
            word-break: break-all;
        }
        .dd-string { color: #a9ff68; }
        .dd-number { color: #ff9f43; }
        .dd-boolean { color: #ff6b81; }
        .dd-null { color: #778ca3; }
        .dd-array { color: #74b9ff; }
        .dd-object { color: #fd79a8; }
    </style>';
    
    echo '<div class="dd-container">';
    echo '<div class="dd-title">🐛 DEBUG OUTPUT</div>';
    
    // Get caller information
    $trace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 2);
    if (isset($trace[0])) {
        $file = $trace[0]['file'] ?? 'unknown';
        $line = $trace[0]['line'] ?? 'unknown';
        echo '<div class="dd-location">📍 ' . basename($file) . ':' . $line . '</div>';
    }
    
    if (empty($vars)) {
        echo '<div class="dd-var">';
        echo '<div class="dd-type">⚠️ NO VARIABLES PROVIDED</div>';
        echo '</div>';
    } else {
        foreach ($vars as $index => $var) {
            echo '<div class="dd-var">';
            
            // Show variable number if multiple variables
            if (count($vars) > 1) {
                echo '<div class="dd-type">VARIABLE #' . ($index + 1) . ' (' . gettype($var) . ')</div>';
            } else {
                echo '<div class="dd-type">TYPE: ' . strtoupper(gettype($var)) . '</div>';
            }
            
            echo '<div class="dd-content">';
            dumpVariable($var);
            echo '</div>';
            echo '</div>';
        }
    }
    
    echo '</div>';
    
    exit(1); // Exit with error code
}

/**
 * Dump a variable with syntax highlighting
 */
function dumpVariable($var, $indent = 0)
{
    $indentStr = str_repeat('  ', $indent);
    
    switch (gettype($var)) {
        case 'string':
            echo '<span class="dd-string">"' . htmlspecialchars($var) . '"</span>';
            echo ' <span class="dd-type">(length: ' . strlen($var) . ')</span>';
            break;
            
        case 'integer':
        case 'double':
            echo '<span class="dd-number">' . $var . '</span>';
            break;
            
        case 'boolean':
            echo '<span class="dd-boolean">' . ($var ? 'true' : 'false') . '</span>';
            break;
            
        case 'NULL':
            echo '<span class="dd-null">null</span>';
            break;
            
        case 'array':
            $count = count($var);
            echo '<span class="dd-array">array(' . $count . ')</span> [' . "\n";
            
            if ($count > 0) {
                foreach ($var as $key => $value) {
                    echo $indentStr . '  ';
                    if (is_string($key)) {
                        echo '"' . htmlspecialchars($key) . '"';
                    } else {
                        echo $key;
                    }
                    echo ' => ';
                    
                    if (is_array($value) || is_object($value)) {
                        echo "\n" . $indentStr . '  ';
                        dumpVariable($value, $indent + 2);
                    } else {
                        dumpVariable($value, $indent + 1);
                    }
                    echo "\n";
                }
            }
            echo $indentStr . ']';
            break;
            
        case 'object':
            $className = get_class($var);
            $reflection = new ReflectionClass($var);
            $properties = $reflection->getProperties();
            
            echo '<span class="dd-object">' . $className . '</span> {' . "\n";
            
            foreach ($properties as $property) {
                $property->setAccessible(true);
                $name = $property->getName();
                $value = $property->getValue($var);
                
                echo $indentStr . '  ';
                
                if ($property->isPrivate()) {
                    echo '<span class="dd-type">private</span> ';
                } elseif ($property->isProtected()) {
                    echo '<span class="dd-type">protected</span> ';
                } else {
                    echo '<span class="dd-type">public</span> ';
                }
                
                echo '$' . $name . ' => ';
                
                if (is_array($value) || is_object($value)) {
                    echo "\n" . $indentStr . '  ';
                    dumpVariable($value, $indent + 2);
                } else {
                    dumpVariable($value, $indent + 1);
                }
                echo "\n";
            }
            echo $indentStr . '}';
            break;
            
        case 'resource':
            echo '<span class="dd-type">resource(' . get_resource_type($var) . ')</span>';
            break;
            
        default:
            echo '<span class="dd-type">' . gettype($var) . '</span>';
            break;
    }
}

/**
 * Dump without dying - just for viewing
 */
function dump(...$vars)
{
    // Capture dd() output
    ob_start();
    dd(...$vars);
    $output = ob_get_clean();
    
    // Remove the exit, just show the output
    echo $output;
    
    // Don't exit, continue execution
}