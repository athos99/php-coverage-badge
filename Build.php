<?php
class Metric
{
    public int $branchesCovered;
    public int $branchesValid;
    public int $linesValid;
    public int $linesCovered;
}
class Build
{

    private function getCloverMetrics(string $cloverMetricsPath): Metric
    {


        $xmlElement = new SimpleXMLElement(file_get_contents($cloverMetricsPath));
        $reportMetrics = $xmlElement->xPath('project/metrics')[0] ?? null;
        if ($reportMetrics === null) {
            throw new \RuntimeException('Could not find metrics in clover report.');
        }
        $metricsAttributes = $reportMetrics->attributes();
        $metric = new Metric();
        $metric->branchesValid = (int) $metricsAttributes->conditionals;
        $metric->branchesCovered = (int) $metricsAttributes->coveredconditionals;
        $metric->linesValid = (int) $metricsAttributes->statements;
        $metric->linesCovered = (int) $metricsAttributes->coveredstatements;
        return $metric;
    }

    private function getCoberturaMetric(string $coberturaMetricsPath): Metric
    {
        $xmlElement = new SimpleXMLElement(file_get_contents($coberturaMetricsPath));

        $metricsAttributes = $xmlElement->attributes() ?? null;
        if ($metricsAttributes === null) {
            throw new \RuntimeException('Could not find metrics in cobertura report.');
        }
        $metric = new Metric();
        $metric->branchesValid = (int) $metricsAttributes->{'branches-valid'};
        $metric->branchesCovered = (int) $metricsAttributes->{'branches-covered'};
        $metric->linesValid = (int) $metricsAttributes->{'lines-valid'};
        $metric->linesCovered = (int) $metricsAttributes->{'lines-covered'};
        return $metric;
    }

    private function buildBadge(int $name, int $covered, int $valid, float $limit, string $outputFile): void
    {
        $coverage = ($valid === 0) ? 100.0 : number_format(($covered * 100) / $valid, 2, '.');

        $color = $coverage >= $limit ? '#4c1' : '#e54';
        $template = file_get_contents(__DIR__ . 'flat.svg');
        $template = str_replace('{{ name }}', $name, $template);
        $template = str_replace('{{ total }}', $coverage, $template);
        $template = str_replace('{{ color }}', $color, $template);
        file_put_contents($outputFile, $template);
    }

    public function run()
    {
        $longopts  = [
            "report::",             // Optional value
            "report-type::",        // Optional value
            "coverage-line-badge-name::", // Optional value
            "coverage-branche-badge-name::", // Optional value
            "coverage-line-badge-path::", // Optional value
            "coverage-branche-badge-path::", // Optional value
            "coverage-line-percent-ok::", // Optional value
            "coverage-branch-percent-ok::", // Optional value
            "push-badge::",         // Optional value
            "repo-token::",         // Optional value
            "commit-message::",     // Optional value
            "commit-email::",       // Optional value

        ];

        $options = getopt("", $longopts);
        $report = $options['report'] ?? 'clover.xml';
        $reportType = $options['report-type'] ?? 'clover';
        $coverageLineBadgeName = $options['coverage-line-badge-name'] ?? 'line coverage';
        $coverageBrancheBadgeName = $options['coverage-branche-badge-name'] ?? 'branche coverage';
        $coverageLineBadgePath = $options['coverage-line-badge-path'] ?? 'coverage_line.svg';
        $coverageBrancheBadgePath = $options['coverage-branche-badge-path'] ?? 'coverage_breanche.svg';
        $coverageLinePercentOk = (float) ($options['coverage-line-percent-ok'] ?? '80');
        $coverageBranchPercentOk = (float) ($options['coverage-branch-percent-ok'] ?? '70');
        $pushBadge = filter_var($options['push-badge'] ?? 'false', FILTER_VALIDATE_BOOLEAN);
        $repoToken = $options['repo-token'] ?? '';
        $commitMessage = $options['commit-message'] ?? 'Update coverage badges';
        $commitEmail = $options['commit-email'] ?? 'github-actions@users.noreply.github.com';

        if ($reportType === 'clover') {
            $metric = $this->getCloverMetrics($report);
        } elseif ($reportType === 'cobertura') {
            $metric = $this->getCoberturaMetric($report);
        } else {
            throw new \RuntimeException('Report type not supported: ' . $reportType);
        }


        $this->buildBadge(
            $coverageLineBadgeName,
            $metric->linesCovered,
            $metric->linesValid,
            $coverageLinePercentOk,
            $coverageLineBadgePath
        );

        $this->buildBadge(
            $coverageBrancheBadgeName,
            $metric->branchesCovered,
            $metric->branchesValid,
            $coverageBranchPercentOk,
            $coverageBrancheBadgePath
        );
    }
}

(new Build())->run();
