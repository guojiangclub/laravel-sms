<?php

namespace iBrand\Sms\Http\Middleware;

use Closure;
use Illuminate\Cache\RateLimiter;
use Symfony\Component\HttpFoundation\Response;

class ThrottleRequests
{
	/**
	 * The rate limiter instance.
	 *
	 * @var \Illuminate\Cache\RateLimiter
	 */
	protected $limiter;

	/**
	 * Create a new request throttler.
	 *
	 * @param \Illuminate\Cache\RateLimiter $limiter
	 */
	public function __construct(RateLimiter $limiter)
	{
		$this->limiter = $limiter;
	}

	/**
	 * Handle an incoming request.
	 *
	 * @param \Illuminate\Http\Request $request
	 * @param \Closure                 $next
	 * @param int                      $maxAttempts
	 * @param int                      $decaySeconds
	 *
	 * @return mixed
	 */
	public function handle($request, Closure $next, $maxAttempts = 60, $decaySeconds = 1)
	{
		$key = $this->resolveRequestSignature($request);

		if ($this->limiter->tooManyAttempts($key, $maxAttempts, $decaySeconds)) {
			return $this->buildResponse($key, $maxAttempts);
		}

		$this->limiter->hit($key, $decaySeconds);

		$response = $next($request);

		return $this->addHeaders(
			$response, $maxAttempts,
			$this->calculateRemainingAttempts($key, $maxAttempts)
		);
	}

	/**
	 * Resolve request signature.
	 *
	 * @param \Illuminate\Http\Request $request
	 *
	 * @return string
	 */
	protected function resolveRequestSignature($request)
	{
		return $request->fingerprint();
	}

	/**
	 * Create a 'too many attempts' response.
	 *
	 * @param $key
	 * @param $maxAttempts
	 *
	 * @return \Symfony\Component\HttpFoundation\Response
	 */
	protected function buildResponse($key, $maxAttempts)
	{
		$message = json_encode([
			'message'     => 'Too many attempts, please slow down the request.',
			'status_code' => 429,
		]);

		$response = new Response($message, 429);

		$retryAfter = $this->limiter->availableIn($key);

		return $this->addHeaders(
			$response, $maxAttempts,
			$this->calculateRemainingAttempts($key, $maxAttempts, $retryAfter),
			$retryAfter
		);
	}

	/**
	 * Add the limit header information to the given response.
	 *
	 * @param \Symfony\Component\HttpFoundation\Response $response
	 * @param                                            $maxAttempts
	 * @param                                            $remainingAttempts
	 * @param null                                       $retryAfter
	 *
	 * @return \Symfony\Component\HttpFoundation\Response
	 */
	protected function addHeaders(Response $response, $maxAttempts, $remainingAttempts, $retryAfter = null)
	{
		$headers = [
			'X-RateLimit-Limit'     => $maxAttempts,
			'X-RateLimit-Remaining' => $remainingAttempts,
		];

		if (!is_null($retryAfter)) {
			$headers['Retry-After']  = $retryAfter;
			$headers['Content-Type'] = 'application/json';
		}

		$response->headers->add($headers);

		return $response;
	}

	/**
	 * Calculate the number of remaining attempts.
	 *
	 * @param string   $key
	 * @param int      $maxAttempts
	 * @param int|null $retryAfter
	 *
	 * @return int
	 */
	protected function calculateRemainingAttempts($key, $maxAttempts, $retryAfter = null)
	{
		if (!is_null($retryAfter)) {
			return 0;
		}

		return $this->limiter->retriesLeft($key, $maxAttempts);
	}
}
