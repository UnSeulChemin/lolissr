<?php

declare(strict_types=1);

use RuntimeException;

final class HttpTestRunner
{
    private const SEPARATOR_LENGTH = 50;

    /**
     * @var array<int, array<string, mixed>>
     */
    private array $results = [];

    private ?string $currentCategory = null;

    /**
     * @param list<array<string, mixed>> $tests
     */
    public function __construct(
        private readonly string $base,
        private readonly array $tests,
        private readonly Stats $stats
    ) {
    }

    // =========================================
    // EXÉCUTION
    // =========================================

    public function run(): int
    {
        $this->printHeader();

        $globalStart = microtime(true);

        foreach ($this->tests as $test)
        {
            $this->runTest($test);
        }

        $totalDuration = microtime(true) - $globalStart;

        $this->printSummary($totalDuration);
        $this->generateReport();

        return $this->stats->hasFailures() ? 1 : 0;
    }

    /**
     * @param array<string, mixed> $test
     */
    private function runTest(array $test): void
    {
        $category = (string) ($test['category'] ?? 'General');
        $label = (string) ($test['label'] ?? 'Sans label');
        $method = strtoupper((string) ($test['method'] ?? 'GET'));
        $path = (string) ($test['path'] ?? '/');
        $expectedStatus = (int) ($test['expected_status'] ?? 200);
        $requestBody = array_key_exists('body', $test)
            ? (string) $test['body']
            : null;

        /** @var list<string> $headers */
        $headers = array_values(
            array_map(
                static fn (mixed $header): string => (string) $header,
                is_array($test['headers'] ?? null)
                    ? $test['headers']
                    : []
            )
        );

        $this->printCategory($category);

        $url = rtrim($this->base, '/') . '/' . ltrim($path, '/');
        $start = microtime(true);

        $response = http_request(
            $method,
            $url,
            $headers,
            $requestBody
        );

        $duration = microtime(true) - $start;

        $status = $response['status'];
        $body = $response['body'];
        $responseHeaders = $response['headers'];

        $failureReason = $this->failureReason(
            $test,
            $status,
            $expectedStatus,
            $body,
            $responseHeaders
        );

        if ($failureReason === null)
        {
            $this->recordSuccess(
                category: $category,
                label: $label,
                method: $method,
                path: $path,
                httpStatus: $status,
                expectedStatus: $expectedStatus,
                duration: $duration,
                headers: $responseHeaders,
                body: $body
            );

            return;
        }

        $this->recordFailure(
            category: $category,
            label: $label,
            method: $method,
            path: $path,
            httpStatus: $status,
            expectedStatus: $expectedStatus,
            duration: $duration,
            reason: $failureReason,
            headers: $responseHeaders,
            body: $body
        );
    }

    // =========================================
    // VALIDATION
    // =========================================

    /**
     * @param array<string, mixed> $test
     * @param list<string> $headers
     */
    private function failureReason(
        array $test,
        int $status,
        int $expectedStatus,
        string $body,
        array $headers
    ): ?string {
        if ($status !== $expectedStatus)
        {
            return "Unexpected status: expected {$expectedStatus}, received {$status}";
        }

        if (! assert_not_empty_body($body))
        {
            return 'Empty response body';
        }

        $reason = $this->containsFailure($test, $body);

        if ($reason !== null)
        {
            return $reason;
        }

        $reason = $this->notContainsFailure($test, $body);

        if ($reason !== null)
        {
            return $reason;
        }

        $expectsJson = (bool) ($test['json'] ?? false);
        $expectsFragment = (bool) ($test['fragment'] ?? false);

        if ($expectsJson && ! assert_json($body))
        {
            return 'Invalid JSON';
        }

        if (! $expectsJson && ! $expectsFragment)
        {
            if (! assert_html($body))
            {
                return 'Invalid HTML';
            }

            if (! assert_title($body))
            {
                return 'Missing title tag';
            }
        }

        return $this->headerFailure($test, $headers);
    }

    /**
     * @param array<string, mixed> $test
     */
    private function containsFailure(array $test, string $body): ?string
    {
        if (! isset($test['contains']))
        {
            return null;
        }

        foreach ((array) $test['contains'] as $needle)
        {
            $needle = (string) $needle;

            if (! assert_contains($body, $needle))
            {
                return 'Missing text: ' . $needle;
            }
        }

        return null;
    }

    /**
     * @param array<string, mixed> $test
     */
    private function notContainsFailure(array $test, string $body): ?string
    {
        if (! isset($test['not_contains']))
        {
            return null;
        }

        foreach ((array) $test['not_contains'] as $needle)
        {
            $needle = (string) $needle;

            if (! assert_not_contains($body, $needle))
            {
                return 'Unexpected text: ' . $needle;
            }
        }

        return null;
    }

    /**
     * @param array<string, mixed> $test
     * @param list<string> $headers
     */
    private function headerFailure(array $test, array $headers): ?string
    {
        if (! isset($test['header_contains']))
        {
            return null;
        }

        foreach ((array) $test['header_contains'] as $expectedHeader)
        {
            $expectedHeader = (string) $expectedHeader;

            if (! assert_header($headers, $expectedHeader))
            {
                return 'Missing header: ' . $expectedHeader;
            }
        }

        return null;
    }

    // =========================================
    // RÉSULTATS
    // =========================================

    /**
     * @param list<string> $headers
     */
    private function recordSuccess(
        string $category,
        string $label,
        string $method,
        string $path,
        int $httpStatus,
        int $expectedStatus,
        float $duration,
        array $headers,
        string $body
    ): void {
        $this->stats->success($duration);

        echo "✅ {$method} {$label} [{$httpStatus}]" . PHP_EOL;

        $this->addResult(
            status: 'OK',
            category: $category,
            label: $label,
            method: $method,
            path: $path,
            httpStatus: $httpStatus,
            expectedStatus: $expectedStatus,
            duration: $duration,
            reason: '',
            headers: $headers,
            body: $body
        );
    }

    /**
     * @param list<string> $headers
     */
    private function recordFailure(
        string $category,
        string $label,
        string $method,
        string $path,
        int $httpStatus,
        int $expectedStatus,
        float $duration,
        string $reason,
        array $headers,
        string $body
    ): void {
        $this->stats->fail($duration);

        echo "❌ {$method} {$label} [{$httpStatus}]";

        if ($reason !== '')
        {
            echo ' -> ' . $reason;
        }

        echo PHP_EOL;

        $this->addResult(
            status: 'FAIL',
            category: $category,
            label: $label,
            method: $method,
            path: $path,
            httpStatus: $httpStatus,
            expectedStatus: $expectedStatus,
            duration: $duration,
            reason: $reason,
            headers: $headers,
            body: $body
        );
    }

    /**
     * @param list<string> $headers
     */
    private function addResult(
        string $status,
        string $category,
        string $label,
        string $method,
        string $path,
        int $httpStatus,
        int $expectedStatus,
        float $duration,
        string $reason,
        array $headers,
        string $body
    ): void {
        $this->results[] = [
            'status' => $status,
            'category' => $category,
            'label' => $label,
            'method' => $method,
            'path' => $path,
            'http_status' => $httpStatus,
            'expected_status' => $expectedStatus,
            'duration' => $duration,
            'reason' => $reason,
            'headers' => ReportSanitizer::headers($headers),
            'body' => $status === 'FAIL'
                ? ReportSanitizer::body($body)
                : ''
        ];
    }

    // =========================================
    // CONSOLE
    // =========================================

    private function printHeader(): void
    {
        echo PHP_EOL;
        echo str_repeat('=', self::SEPARATOR_LENGTH) . PHP_EOL;
        echo 'LOLISSR HTTP TESTS' . PHP_EOL;
        echo str_repeat('=', self::SEPARATOR_LENGTH) . PHP_EOL;
        echo PHP_EOL;
    }

    private function printCategory(string $category): void
    {
        if ($category === $this->currentCategory)
        {
            return;
        }

        $this->currentCategory = $category;

        echo PHP_EOL;
        echo '--- ' . $category . ' ---' . PHP_EOL;
    }

    private function printSummary(float $totalDuration): void
    {
        echo PHP_EOL;
        echo str_repeat('=', self::SEPARATOR_LENGTH) . PHP_EOL;
        echo 'Tests   : ' . $this->stats->total() . PHP_EOL;
        echo 'OK      : ' . $this->stats->successCount() . PHP_EOL;
        echo 'FAIL    : ' . $this->stats->failCount() . PHP_EOL;
        echo 'Success : ' . $this->stats->successRate() . '%' . PHP_EOL;
        echo 'Moyenne : ' . round($this->stats->averageDuration() * 1000, 2) . 'ms' . PHP_EOL;
        echo 'Temps   : ' . round($totalDuration, 3) . 's' . PHP_EOL;
        echo str_repeat('=', self::SEPARATOR_LENGTH) . PHP_EOL;
    }

    // =========================================
    // RAPPORT
    // =========================================

    private function generateReport(): void
    {
        $reportDirectory = dirname(__DIR__) . '/reports';

        if (
            ! is_dir($reportDirectory)
            && ! mkdir($reportDirectory, 0755, true)
            && ! is_dir($reportDirectory)
        ) {
            throw new RuntimeException(
                'Impossible de créer le dossier reports.'
            );
        }

        $reportFile = $reportDirectory . '/lolissr-http-report.html';

        HtmlReport::generate(
            $this->results,
            $this->stats,
            $reportFile
        );

        echo PHP_EOL;
        echo '📄 Rapport HTML généré' . PHP_EOL;
        echo $reportFile . PHP_EOL;
    }
}