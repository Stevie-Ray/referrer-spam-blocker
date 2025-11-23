<?php

declare(strict_types=1);

namespace StevieRay\CLI;

use StevieRay\Generator;
use RuntimeException;

class CommandLineInterface
{
    private const array SUPPORTED_TYPES = [
        'apache',
        'nginx',
        'varnish',
        'iis',
        'uwsgi',
        'caddy',
        'caddy2',
        'haproxy',
        'lighttpd',
        'google',
    ];

    /**
     * @param array<int, string> $argv
     */
    public function run(array $argv): int
    {
        $options = $this->parseOptions($argv);

        if ($options['help']) {
            $this->showHelp();

            return 0;
        }

        if ($options['version']) {
            $this->showVersion();

            return 0;
        }

        try {
            $generator = new Generator($options['output']);

            if ($options['dry-run']) {
                $this->showDryRun($generator);

                return 0;
            }

            if ($options['types'] !== []) {
                $generator->generateSpecificConfigs($options['types']);
                echo 'Generated specific configuration files: ' . implode(', ', $options['types']) . "\n";
            } else {
                $generator->generateFiles();
                echo "Generated all configuration files.\n";
            }

            $this->showStatistics($generator);

            return 0;
        } catch (\Exception $e) {
            $this->showError($e->getMessage());

            return 1;
        }
    }

    /**
     * Parse command line options
     *
     * @param array<int, string> $argv
     * @return array{
     *   help: bool,
     *   version: bool,
     *   'dry-run': bool,
     *   output: string,
     *   types: array<int, string>
     * }
     */
    private function parseOptions(array $argv): array
    {
        $options = [
            'help' => false,
            'version' => false,
            'dry-run' => false,
            'output' => __DIR__ . '/../../',
            'types' => [],
        ];

        $args = array_slice($argv, 1); // Skip script name

        for ($i = 0; $i < count($args); $i++) {
            $arg = $args[$i];

            match ($arg) {
                '--help', '-h' => $options['help'] = true,
                '--version', '-v' => $options['version'] = true,
                '--dry-run' => $options['dry-run'] = true,
                '--output', '-o' => $this->handleOutputOption($args, $i, $options),
                '--types', '-t' => $this->handleTypesOption($args, $i, $options),
                default => throw new RuntimeException("Unknown option: $arg"),
            };
        }

        return $options;
    }

    /**
     * Handle --output option
     *
     * @param array<int, string> $args
     * @param int $i
     * @param array{help: bool, version: bool, 'dry-run': bool, output: string, types: array<int, string>} $options
     * @return void
     */
    private function handleOutputOption(array $args, int &$i, array &$options): void
    {
        if (isset($args[$i + 1])) {
            $options['output'] = $args[++$i];
        } else {
            throw new RuntimeException('--output requires a directory path');
        }
    }

    /**
     * Handle --types option
     *
     * @param array<int, string> $args
     * @param int $i
     * @param array{help: bool, version: bool, 'dry-run': bool, output: string, types: array<int, string>} $options
     * @return void
     */
    private function handleTypesOption(array $args, int &$i, array &$options): void
    {
        if (isset($args[$i + 1])) {
            $types = explode(',', $args[++$i]);
            $types = array_map('trim', $types);

            foreach ($types as $type) {
                if (!in_array($type, self::SUPPORTED_TYPES, true)) {
                    throw new RuntimeException(
                        "Unsupported type: $type. Supported types: " . implode(', ', self::SUPPORTED_TYPES)
                    );
                }
            }

            $options['types'] = $types;
        } else {
            throw new RuntimeException('--types requires a comma-separated list');
        }
    }

    /**
     * Show help information
     */
    private function showHelp(): void
    {
        echo "Referrer Spam Blocker Generator\n\n";
        echo "Usage: php run.php [options]\n\n";
        echo "Options:\n";
        echo "  -h, --help              Show this help message\n";
        echo "  -v, --version           Show version information\n";
        echo "  --dry-run               Show what would be generated without creating files\n";
        echo "  -o, --output <dir>      Output directory (default: project root)\n";
        echo "  -t, --types <list>      Generate only specific types (comma-separated)\n";
        echo '                          Supported types: ' . implode(', ', self::SUPPORTED_TYPES) . "\n\n";
        echo "Examples:\n";
        echo "  php run.php                                    # Generate all configs\n";
        echo "  php run.php --types apache,nginx              # Generate only Apache and Nginx\n";
        echo "  php run.php --output /tmp --dry-run           # Show what would be generated\n";
        echo "  php run.php --types google --output ./config  # Generate only Google Analytics files\n\n";
    }

    /**
     * Show version information
     */
    private function showVersion(): void
    {
        echo "Referrer Spam Blocker Generator\n";
    }

    /**
     * Show dry run information
     *
     * @param Generator $generator
     */
    private function showDryRun(Generator $generator): void
    {
        echo "DRY RUN - No files will be created\n\n";

        $stats = $generator->getStatistics();
        echo "Would generate:\n";
        echo "- Total domains: {$stats['total_domains']}\n";
        echo "- Configuration files: {$stats['config_files']}\n";
        echo "- Output directory: {$stats['output_directory']}\n\n";

        echo "Configuration types:\n";
        foreach ($generator->getConfigGenerators() as $generator) {
            echo "- {$generator->getDescription()} ({$generator->getFilename()})\n";
        }
        echo "\n";
    }

    /**
     * Show generation statistics
     *
     * @param Generator $generator
     */
    private function showStatistics(Generator $generator): void
    {
        $stats = $generator->getStatistics();

        echo "\nGeneration completed successfully!\n";
        echo "Statistics:\n";
        echo "- Total domains processed: {$stats['total_domains']}\n";
        echo '- Configuration files generated: ' . count($stats['generated_files']) . "\n";
        echo "- Output directory: {$stats['output_directory']}\n";

        if ($stats['generated_files'] !== []) {
            echo "\nGenerated files:\n";
            foreach ($stats['generated_files'] as $file) {
                echo "- $file\n";
            }
        }
        echo "\n";
    }

    /**
     * Show error message
     *
     * @param string $message
     */
    private function showError(string $message): void
    {
        echo "Error: $message\n";
        echo "Use --help for usage information.\n";
    }
}
