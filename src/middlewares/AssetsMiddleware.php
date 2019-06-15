<?php

namespace momentphp\middlewares;

/**
 * AssetMiddleware
 */
class AssetsMiddleware extends \momentphp\Middleware
{
    /**
     * Invoke middleware
     *
     * @param \Psr\Http\Message\RequestInterface $request
     * @param \Psr\Http\Message\ResponseInterface $response
     * @param callable $next
     * @return \Psr\Http\Message\ResponseInterface
     */
    public function __invoke($request, $response, $next)
    {
        if (!$request->isGet()) {
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
            new \Slim\Http\Body(fopen($file, 'r'))
        );
    }

    /**
     * Return file mime type
     *
     * @param  string $filename
     * @return string
     */
    protected function mimeType($file)
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
        } elseif (function_exists('finfo_open')) {
            $finfo = finfo_open(FILEINFO_MIME);
            $mimeType = finfo_file($finfo, $file);
            finfo_close($finfo);
            return $mimeType;
        } else {
            return 'application/octet-stream';
        }
    }
}
