<?php

use Symfony\Component\HttpFoundation\File\UploadedFile;

class LaravelTestCase extends Illuminate\Foundation\Testing\TestCase
{
    /**
     * The base URL to use while testing the application.
     *
     * @var string
     */
    protected $baseUrl = 'http://backend.zoomtivity.app.com';

    /**
     * Creates the application.
     *
     * @return \Illuminate\Foundation\Application
     */
    public function createApplication()
    {
        $app = require __DIR__.'/../bootstrap/app.php';

        $app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

        return $app;
    }

    /**
     * Setup the test environment.
     *
     * @return void
     */
    public function setUp()
    {
        parent::setUp();
        Session::start();
    }

    /**
     * Visit the given URI with a POST request.
     *
     * @param  string $uri
     * @param  array $data
     * @param  array $headers
     * @param array $files
     * @return $this
     */
    public function post($uri, array $data = [], array $headers = [], array $files = [])
    {
        $server = $this->transformHeadersToServerVars($headers);

        $this->call('POST', $uri, $data, [], $files, $server);

        return $this;
    }

    /**
     * Visit the given URI with a PUT request.
     *
     * @param  string $uri
     * @param  array $data
     * @param  array $headers
     * @param array $files
     * @return $this
     */
    public function put($uri, array $data = [], array $headers = [], array $files = [])
    {
        $server = $this->transformHeadersToServerVars($headers);

        $this->call('PUT', $uri, $data, [], $files, $server);

        return $this;
    }

    /**
     * Visit the given URI with a PATCH request.
     *
     * @param  string $uri
     * @param  array $data
     * @param  array $headers
     * @param array $files
     * @return $this
     */
    public function patch($uri, array $data = [], array $headers = [], array $files = [])
    {
        $server = $this->transformHeadersToServerVars($headers);

        $this->call('PATCH', $uri, $data, [], $files, $server);

        return $this;
    }
    
    protected function makeUploadedFile()
    {
        $faker = \Faker\Factory::create();
        $path = $faker->image(storage_path('/test'));
        $file = new SplFileInfo($path);
        $file = new UploadedFile($path, $file->getFilename(), 'image/jpeg', $file->getSize(), 0);

        return $file;
    }
}
