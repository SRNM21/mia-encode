<?php

namespace App\Http\Request;

class Request
{
    protected array $get;
    protected array $post;
    protected array $request;
    protected array $files;
    protected array $cookies;
    protected array $server;

    public function __construct(
        array $get,
        array $post,
        array $request,
        array $files,
        array $cookies,
        array $server
    ) {
        $this->get     = $get;
        $this->post    = $post;
        $this->request = $request;
        $this->files   = $files;
        $this->cookies = $cookies;
        $this->server  = $server;
    }

    /**
     * Create instance from PHP globals
     */
    public static function capture(): static
    {
        return new static(
            $_GET,
            $_POST,
            $_REQUEST,
            $_FILES,
            $_COOKIE,
            $_SERVER,
        );
    }

    /**
     * Get all input
     */
    public function all(): array
    {
        return array_merge(
            $this->get, 
            $this->post,
            $this->request,
            $this->files,
            $this->cookies,
            $this->server,
            $this->parseInput()
        );
    }

    /**
     * Get specific input value
     */
    public function input(string $key, $default = null)
    {
        return $this->request[$key]
            ?? $this->get[$key]
            ?? $this->post[$key]    
            ?? $this->parseInput()[$key]
            ?? $default;
    }

    /**
     * Get all POST input
     */
    public function posts()
    {
        return $this->post;
    }

    /**
     * Get only POST input
     */
    public function post(string $key, $default = null)
    {
        return $this->post[$key] ?? $default;
    }

    /**
     * Get all GET input
     */
    public function queries()
    {
        return $this->get;
    }

    /**
     * Get only GET input
     */
    public function get(string $key, $default = null)
    {
        return $this->get[$key] ?? $default;
    }

    /**
     * Check request method
     */
    public function method(): string
    {
        return strtoupper($this->server['REQUEST_METHOD'] ?? 'GET');
    }

    /**
     * Determine if request is GET
     */
    public function isGet(): bool
    {
        return $this->method() === 'GET';
    }

    /**
     * Determine if request is POST
     */
    public function isPost(): bool
    {
        return $this->method() === 'POST';
    }

    /**
     * Determine if request is PUT
     */
    public function isPut(): bool
    {
        return $this->method() === 'PUT';
    }

    /**
     * Determine if request is PATCH
     */
    public function isPatch(): bool
    {
        return $this->method() === 'PATCH';
    }

    /**
     * Determine if request is DELETE
     */
    public function isDelete(): bool
    {
        return $this->method() === 'DELETE';
    }
    
    /**
     * Get request URI
     */
    public function uri(): string
    {
        return strtok($this->server['REQUEST_URI'] ?? '/', '?');
    }

    /**
     * Get uploaded file
     */
    public function file(string $key)
    {
        return $this->files[$key] ?? null;
    }

    /**
     * Get cookie value
     */
    public function cookie(string $key, $default = null)
    {
        return $this->cookies[$key] ?? $default;
    }

    function parseInput()
	{
        $data = file_get_contents("php://input");

        if (!$data) return [];

        $contentType = $this->server['CONTENT_TYPE'] ?? '';

        if (stripos($contentType, 'application/json') !== false) {
            $json = json_decode($data, true);
            return is_array($json) ? $json : [];
        }

        // fallback for urlencoded data
        parse_str($data, $result);
        return $result;
	}
}
