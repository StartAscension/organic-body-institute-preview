<?php
declare( strict_types=1 );

namespace RankingCoach\Inc\Core\Api\Feedback;

use Exception;
use RankingCoach\Inc\Core\Base\Traits\RcLoggerTrait;
use RankingCoach\Inc\Core\Helpers\CoreHelper;
use RankingCoach\Inc\Exceptions\HttpApiException;
use RankingCoach\Inc\Core\Api\HttpApiClient;
use ReflectionException;

/**
 * Class FeedbackApiManager
 * Handles feedback submission to RankingCoach API
 */
class FeedbackApiManager extends HttpApiClient
{
    use RcLoggerTrait;

	/**
	 * Singleton instance
	 */
	protected static ?FeedbackApiManager $instance = null;

    /**
	 * Get the singleton instance.
	 *
	 * @param array $defaultHeaders
	 * @param bool|null $bearerToken
	 *
	 * @return FeedbackApiManager
	 * @throws HttpApiException
	 * @throws ReflectionException
	 * @throws Exception
	 */
	public static function getInstance( array $defaultHeaders = [], ?bool $bearerToken = false ): FeedbackApiManager {
		if ( ! self::$instance ) {
			if($bearerToken) {
				$accessToken = self::handleTokenValidation();
			}
			self::$instance = new self($defaultHeaders, $accessToken ?? null);
		}

		return self::$instance;
	}

	/**
	 * FeedbackApiManager constructor.
	 *
	 * @param array $defaultHeaders
	 * @param string|null $accessToken
	 *
	 * @throws Exception
	 */
	public function __construct(
		array $defaultHeaders = [],
		?string $accessToken = null
	) {
		parent::__construct($defaultHeaders, $accessToken);
	}

	/**
	 * Submit deactivation feedback to the API.
	 *
	 * @param string $reasonCode The reason code for deactivation
	 * @param string $feedbackText The feedback text from the user
	 * @param bool $deleteProject Whether to delete project data on deactivation
	 * @param bool $cancelAccount Whether to cancel the account
	 *
	 * @return bool True if feedback was submitted successfully, false otherwise
     * @throws Exception
	 */
	public function submitFeedback(
		string $reasonCode,
		string $feedbackText = '',
		bool $deleteProject = false,
		bool $cancelAccount = false
	): bool {
		try {
			// Set the API endpoint URL to publicApi
			$this->setUrl('feedback', 'publicApi', false);

			// Prepare the security payload with feedback data
			$payload = CoreHelper::generateCommonSecurityPayload([
				'reasonCode' => $reasonCode,
				'feedbackText' => $feedbackText,
				'deleteProject' => $deleteProject,
				'cancelAccount' => $cancelAccount,
			]);

			$this->prepareSecurityHeaders($this->getBearerToken(), $payload);

			// Send the POST request to the API
			$response = $this->post($payload);

			// Log the request
			$this->log_json([
				'operation_type' => 'feedback_submission',
				'operation_status' => 'success',
				'context_entity' => 'feedback',
				'context_type' => 'deactivation',
				'metadata' => [
					'reason_code' => $reasonCode,
					'delete_project' => $deleteProject,
					'cancel_account' => $cancelAccount,
					'response' => $response,
				],
			], 'feedback');

			return true;
		} catch ( Exception $e ) {
			$this->log_json([
				'operation_type' => 'feedback_submission',
				'operation_status' => 'error',
				'context_entity' => 'feedback',
				'context_type' => 'deactivation',
				'error_details' => [
					'exception_message' => $e->getMessage(),
					'exception_code' => $e->getCode(),
					'exception_file' => $e->getFile(),
					'exception_line' => $e->getLine(),
				],
			], 'feedback');

			return false;
		}
	}

}
