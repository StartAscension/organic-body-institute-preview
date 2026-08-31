<?php
declare( strict_types=1 );

namespace RankingCoach\Inc\Core\Api;

use Exception;
use RankingCoach\Inc\Exceptions\UnsupportedContentTypeException;
use RankingCoach\Inc\Exceptions\HttpApiException;
use RankingCoach\Inc\Exceptions\ResponseValidationException;
use function rceh;

/**
 * Class HttpAPIResponseHandler
 */
class HttpAPIResponseHandler {

	/** @var array $response The response. */
	public array $response;

	/**
	 * Validate the response.
	 *
	 * @param array|null $response The response.
	 *
	 * @throws Exception
	 */
	public function validate(?array $response = null): static
	{
		$response = $response ?? $this->response;
		$statusCode = wp_remote_retrieve_response_code($response);
		if ($statusCode >= 400) {
			$content = wp_remote_retrieve_body($response);
			$data = json_decode($content, true);

			$error = 'Unknown error';
			$message = '';

			if (json_last_error() !== JSON_ERROR_NONE) {
				$error = 'Invalid JSON response';
				$decodedContent = htmlspecialchars_decode($content);
				$doctypePosition = strrpos($decodedContent, '<!DOCTYPE');
				if ($doctypePosition !== false) {
					$decodedContent = substr($decodedContent, 0, $doctypePosition);
				}
				$message = trim($decodedContent);
			} else {
				if (is_array($data)) {
					$error = isset($data['error']) && is_string($data['error']) ? trim($data['error']) : 'Unknown error';
					$message = isset($data['message']) && is_string($data['message']) ? trim($data['message']) : '';
				}
			}

			$parts = array_filter(array_map(static fn($part) => is_string($part) ? trim($part) : '', [ $error, $message ]), static fn($part) => $part !== '');
			$errorText = $parts ? implode(' - ', array_unique($parts)) : 'Unknown error';
			$exception = new HttpApiException($errorText, $statusCode);

			throw $exception;
		}
		return $this;
	}

	/**
	 * Parse the response.
	 *
	 * @param array|null $response The response.
	 *
	 * @return array
	 *
	 * @throws Exception
	 */
	public function parse(?array $response = null): array
	{
		$response = $response ?? $this->response;
		$content = wp_remote_retrieve_body($response);
		$contentType = wp_remote_retrieve_header($response, 'content-type');

		if (str_contains($contentType, 'application/json')) {
			$data = json_decode($content);
			if (json_last_error() !== JSON_ERROR_NONE) {
				rceh()->error( new ResponseValidationException('Error decoding JSON response: ' . json_last_error_msg() ), $content );
			}
			return ['content' => $data];
		} elseif (str_contains($contentType, 'text/plain') || str_contains($contentType, 'text/html')) {
			return ['content' => $content];
		} else {
			rceh()->error( new UnsupportedContentTypeException("Unsupported content type: $contentType"), $content );
		}
	}
}
