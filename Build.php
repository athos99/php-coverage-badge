<?php

include_once 'Dict.php';


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
        $metric->branchesValid = intval($metricsAttributes->conditionals);
        $metric->branchesCovered = intval($metricsAttributes->coveredconditionals);
        $metric->linesValid = intval($metricsAttributes->statements);
        $metric->linesCovered = intval($metricsAttributes->coveredstatements);
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
        $metric->branchesValid = intval($metricsAttributes->{'branches-valid'});
        $metric->branchesCovered = intval($metricsAttributes->{'branches-covered'});
        $metric->linesValid = intval($metricsAttributes->{'lines-valid'});
        $metric->linesCovered = intval($metricsAttributes->{'lines-covered'});
        return $metric;
    }


    private function  calculateTextLength110(string $s)
    {

        if ($s == null || strlen($s) == 0) {
            return 0;
        }
        $total = 0;
        $len = mb_strlen($s);
        for ($i = 0; $i < $len; $i++) {
            $c = mb_substr($s, $i, 1);
            if (array_key_exists($c, Dict::$defaultWidths["character-lengths"])) {
                $total += Dict::$defaultWidths["character-lengths"][$c];
            } else {
                $total += Dict::$defaultWidths["mean-character-length"];
            }
            if ($i > 0) {
                $pair = mb_substr($s, $i - 1, 2, 'UTF-8');
                if (array_key_exists($pair, Dict::$defaultWidths["kerning-pairs"])) {
                    $total -= Dict::$defaultWidths["kerning-pairs"][$pair];
                }
            }
        }
        return $total;
    }



    private function buildBadge(string $name,  int $covered, int $valid, float $limit, string $outputFile): void
    {
        $coverage = (($valid === 0) ? 100.0 : number_format(($covered * 100) / $valid, 1, '.')) . '%';
        $color = $coverage >= $limit ? '#4c1' : '#e54';
        $template = file_get_contents('template.svg');
        $coverageLength = $this->calculateTextLength110($coverage);
        $nameTextLength = $this->calculateTextLength110($name);
        $rightWidth = ceil($coverageLength / 10) + 10;
        $leftWidth = ceil($nameTextLength / 10) + 10;
        $badgeWidth = $leftWidth + $rightWidth;
        # The -10 below is for an exta buffer on right end of badge
        $rightCenter = 10 * $leftWidth + $rightWidth * 5 - 10;
        # The +10 below is for an extra buffer on left end of badge
        $leftCenter = 10 + $leftWidth * 5;
        $template = str_replace('{{ name }}', $name, $template);
        $template = str_replace('{{ nameTextLength }}', $nameTextLength, $template);
        $template = str_replace('{{ coverage }}', $coverage, $template);
        $template = str_replace('{{ coverageLength }}', $coverageLength, $template);
        $template = str_replace('{{ color }}', $color, $template);
        $template = str_replace('{{ rightWidth }}', $rightWidth, $template);
        $template = str_replace('{{ badgeWidth }}', $badgeWidth, $template);
        $template = str_replace('{{ rightCenter }}', $rightCenter, $template);
        $template = str_replace('{{ leftWidth }}', $leftWidth, $template);
        $template = str_replace('{{ leftCenter }}', $leftCenter, $template);
        file_put_contents($outputFile, $template);
    }

    public function run()
    {
        $longopts  = [
            "report::",             // Optional value
            "report-type::",        // Optional value
            "coverage-line-badge-name::", // Optional value
            "coverage-branche-badge-name::", // Optional value
            "coverage-line-badge-width::", // Optional value
            "coverage-branche-badge-width::", // Optional value
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
        $coverageLinePercentOk = floatval($options['coverage-line-percent-ok'] ?? '80');
        $coverageBranchPercentOk = floatval($options['coverage-branch-percent-ok'] ?? '70');
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
