<?php
/**
 * OriginPHP Framework
 * Copyright 2018 - 2020 Jamiel Sharief.
 *
 * Licensed under The MIT License
 * The above copyright notice and this permission notice shall be included in all copies or substantial
 * portions of the Software.
 *
 * @copyright   Copyright (c) Jamiel Sharief
 * @link        https://www.originphp.com
 * @license     https://opensource.org/licenses/mit-license.php MIT License
 */

namespace Origin\Test\DotEnv;

use \Exception;
use Origin\DotEnv\DotEnv;
use \InvalidArgumentException;

class MockDotEnv extends DotEnv
{
    protected $env = [];
    protected function env(string $key, $value) : void
    {
        $this->env[$key] = $value;
    }
    public function getEnv()
    {
        return $this->env;
    }
}

class DotEnvTest extends \PHPUnit\Framework\TestCase
{
    public function testLoadAndParsing()
    {
        $dotenv = new MockDotEnv();
        $dotenv->load(__DIR__, '.env.test'); // TestApp
        
        $results = $dotenv->getEnv();
  
        $this->assertEquals('3a4e41e63f65b228e2927d6045c09577', md5(json_encode($results)));
    }

    public function testLoadExecption()
    {
        $dotenv = new MockDotEnv();
        $this->expectException(InvalidArgumentException::class);
        $dotenv->load(__DIR__);
    }

    public function testLoadReal()
    {
        $dotenv = new DotEnv();

        $directory = sys_get_temp_dir();
        $file = uniqid();

        $value = bin2hex(random_bytes('32'));
        file_put_contents($directory . '/' . $file, "ENVTEST_UUID={$value}\n");

        $dotenv->load($directory, $file); // TestApp
        $this->assertEquals($value, $_ENV['ENVTEST_UUID']);
    }

    public function testMultiLine()
    {
        $key = <<< EOF
ENVTEST_KEY="-----BEGIN RSA PRIVATE KEY-----
...
AbDE7...
...
-----END RSA PRIVATE KEY-----"
EOF;
        $dotenv = new DotEnv();
        $directory = sys_get_temp_dir();
        $file = uniqid();
        
        file_put_contents($directory . '/' . $file, $key);
        $dotenv->load($directory, $file);
        $this->assertEquals('05a378a68f104bb1a076fed1c5d770ea', md5($_ENV['ENVTEST_KEY']));
    }

    public function testMultiLineException()
    {
        $key = <<< EOF
ENVTEST_KEY="This is
a multi line that does not
end with a quotation
EOF;
        $dotenv = new DotEnv();
        $directory = sys_get_temp_dir();
        $file = uniqid();
        
        file_put_contents($directory . '/' . $file, $key);
      
        $this->expectException(Exception::class);
        $dotenv->load($directory, $file);
    }
}
