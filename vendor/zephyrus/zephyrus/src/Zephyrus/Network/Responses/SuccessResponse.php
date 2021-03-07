<?php namespace Zephyrus\Network\Responses;

use Zephyrus\Network\ContentType;
use Zephyrus\Network\Response;

trait SuccessResponse
{
    /**
     * Renders the given data as json string.
     *
     * @param mixed $data
     * @return Response
     */
    public function json($data): Response
    {
        $response = new Response(ContentType::JSON, 200);
        $response->setContent(json_encode($data));
        return $response;
    }

    /**
     * Renders the given data as plain string.
     *
     * @param mixed $data
     * @return Response
     */
    public function plain($data): Response
    {
        $response = new Response(ContentType::PLAIN, 200);
        $response->setContent($data);
        return $response;
    }

    /**
     * Throws an HTTP "201 Created" header that should be used with api compliant
     * post response. Needs a redirect url (will send the location header just like
     * a regular redirection). Optionally, can include a content body (e.g. JSON
     * response of the created element).
     *
     * @param string $redirectUrl
     * @param string $content
     * @param string $contentType
     * @return Response
     */
    public function created(string $redirectUrl, string $content = "", string $contentType = ContentType::PLAIN): Response
    {
        $response = new Response($contentType, 201);
        $response->setContent($content);
        $response->addHeader('Location', $redirectUrl);
        return $response;
    }

    /**
     * Creates a simple no content response (204) that should be sent in response to a PUT request.
     *
     * @return Response
     */
    public function noContent(): Response
    {
        return new Response(ContentType::PLAIN, 204);
    }

    /**
     * Renders a given file as a downloadable content with application/octet-stream content type. If no filename is
     * given, it will automatically use the actual file basename. If the deleteAfter argument is set to true, it will
     * automatically remove the file after sending it. Useful for temporary files.
     *
     * @param string $filePath
     * @param null|string $filename
     * @param bool $deleteAfter
     * @return Response
     */
    public function download(string $filePath, ?string $filename = null, bool $deleteAfter = false): Response
    {
        if (!file_exists($filePath)) {
            throw new \InvalidArgumentException("Specified file doesn't exists");
        }
        if (is_null($filename)) {
            $filename = basename($filePath);
        }
        $contentLength = filesize($filePath);
        $response = new Response(ContentType::APPLICATION, 200);
        $this->addFileTransferHeaders($response);
        $response->addHeader("Content-Disposition", 'attachment; filename="' . $filename . '"');
        $response->addHeader("Content-Length", $contentLength);
        $response->setContentCallback(function () use ($filePath, $deleteAfter) {
            @readfile($filePath);
            if ($deleteAfter) {
                unlink($filePath);
            }
        });
        return $response;
    }

    /**
     * Creates a response as a downloadable file with the specified content. By default, will send it as content type
     * application/octet-stream, but can be changed to reflect the content's nature more closely (e.g. calendar, json,
     * etc.).
     *
     * @param string $content
     * @param string $filename
     * @param string $contentType
     * @return Response
     */
    public function downloadContent(string $content, string $filename, string $contentType = ContentType::APPLICATION): Response
    {
        $contentLength = strlen($content);
        $response = new Response($contentType, 200);
        $response->setContent($content);
        $this->addFileTransferHeaders($response);
        $response->addHeader("Content-Disposition", 'attachment; filename="' . $filename . '"');
        $response->addHeader("Content-Length", $contentLength);
        return $response;
    }

    /**
     * Redirect user to specified URL. Throws an HTTP "303 See Other" header instead of the default 301. This indicates,
     * more precisely, that the response is elsewhere.
     *
     * @param string $url
     * @return Response
     */
    public function redirect(string $url): Response
    {
        $response = new Response(ContentType::PLAIN, 303);
        $response->addHeader('Location', $url);
        return $response;
    }

    /**
     * Adds the required basic file transfer HTTP headers such as expires, pragma, cache-control, encoding, etc.
     *
     * @param Response $response
     */
    private function addFileTransferHeaders(Response $response)
    {
        $response->addHeader("Pragma", "public");
        $response->addHeader("Expires", "0");
        $response->addHeader("Cache-Control", "must-revalidate, post-check=0, pre-check=0");
        $response->addHeader("Cache-Control", "public");
        $response->addHeader("Content-Description", "File Transfer");
        $response->addHeader("Content-Transfer-Encoding", "binary");
    }
}
