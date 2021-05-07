<?php

namespace momentphp\middlewares;

use momentphp\Middleware;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Slim\Psr7\Factory\StreamFactory;

/**
 * AssetMiddleware
 */
class AssetsMiddleware extends Middleware
{
    /**
     * Invoke middleware
     *
     * @param RequestInterface $request
     * @param ResponseInterface $response
     * @param callable $next
     * @return ResponseInterface
     */
    public function __invoke(RequestInterface $request, ResponseInterface $response, callable $next): ResponseInterface
    {
        if ($request->getMethod() === 'GET') {
            return $next($request, $response);
        }
        $path = $request->getUri()->getPath();
        $path = ltrim($path, '/');
        $path = explode('/', $path);
        $prefix = array_shift($path);
        if ($prefix !== 'bundles') {
            return $next($request, $response);
        }
        $alias = array_shift($path);
        if (!$this->container()->get('app')->bundles($alias)) {
            return $next($request, $response);
        }
        $bundle = $this->container()->get('app')->bundles($alias);
        array_unshift($path, $bundle::classPath('web'));
        $file = path($path);
        if (!is_file($file)) {
            return $next($request, $response);
        }
        return $response->withStatus(200)->withHeader('Content-Type', $this->mimeType($file))->withBody(
            (new StreamFactory())->createStreamFromFile($file)
        );
    }

    /**
     * Return file mime type
     *
     * @param string $file
     * @return string
     */
    protected function mimeType(string $file): string
    {
        $mimeTypes = [
            'txt' => 'text/plain',
            'htm' => 'text/html',
            'html' => 'text/html',
            'php' => 'text/html',
            'css' => 'text/css',
            'js' => 'application/javascript',
            'json' => 'application/json',
            'xml' => 'application/xml',
            'swf' => 'application/x-shockwave-flash',
            'flv' => 'video/x-flv',

            // images
            'png' => 'image/png',
            'jpe' => 'image/jpeg',
            'jpeg' => 'image/jpeg',
            'jpg' => 'image/jpeg',
            'gif' => 'image/gif',
            'bmp' => 'image/bmp',
            'ico' => 'image/vnd.microsoft.icon',
            'tiff' => 'image/tiff',
            'tif' => 'image/tiff',
            'svg' => 'image/svg+xml',
            'svgz' => 'image/svg+xml',

            // archives
            'zip' => 'application/zip',
            'rar' => 'application/x-rar-compressed',
            'exe' => 'application/x-msdownload',
            'msi' => 'application/x-msdownload',
            'cab' => 'application/vnd.ms-cab-compressed',

            // audio/video
            'mp3' => 'audio/mpeg',
            'qt' => 'video/quicktime',
            'mov' => 'video/quicktime',

            // adobe
            'pdf' => 'application/pdf',
            'psd' => 'image/vnd.adobe.photoshop',
            'ai' => 'application/postscript',
            'eps' => 'application/postscript',
            'ps' => 'application/postscript',

            // ms office
            'doc' => 'application/msword',
            'rtf' => 'application/rtf',
            'xls' => 'application/vnd.ms-excel',
            'ppt' => 'application/vnd.ms-powerpoint',

            // open office
            'odt' => 'application/vnd.oasis.opendocument.text',
            'ods' => 'application/vnd.oasis.opendocument.spreadsheet',
        ];
        $fileArr = explode('.', basename($file));
        $ext = strtolower(array_pop($fileArr));
        if (array_key_exists($ext, $mimeTypes)) {
            return $mimeTypes[$ext];
        }

        if (function_exists('finfo_open')) {
            $finfo = finfo_open(FILEINFO_MIME);
            $mimeType = finfo_file($finfo, $file);
            finfo_close($finfo);
            return $mimeType;
        }

        return 'application/octet-stream';
    }
}
