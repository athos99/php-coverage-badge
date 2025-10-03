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

    private function buildBadge(int $name, int $covered, int $valid)
    {
        $coverage = ($valid === 0) ? 100.0 : number_format(($covered * 100) / $valid, 2, '.');
        if ($coverage < 40) {
            $color = '#e05d44';  // Red
        } elseif ($coverage < 60) {
            $color = '#fe7d37';  // Orange
        } elseif ($coverage < 75) {
            $color = '#dfb317';  // Yellow
        } elseif ($coverage < 90) {
            $color = '#a4a61d';  // Yellow-Green
        } elseif ($coverage < 95) {
            $color = '#97CA00';  // Green
        } elseif ($coverage <= 100) {
            $color = '#4c1';     // Bright Green
        } else {
            $color = '#a4a61d';      // Default Gray
        }

        $template = file_get_contents(__DIR__ . 'flat.svg');
        $template = str_replace('{{ name }}', $name, $template);
        $template = str_replace('{{ total }}', $coverage, $template);
        $template = str_replace('{{ color }}', $color, $template);
        file_put_contents($outputFile, $template);
    }




    public function test()
    {
        $metric = $this->getCloverMetrics('clover.xml');
        //  var_dump($metric);

        $metric = $this->getCoberturaMetric('cobertura.xml');
        var_dump($metric);
    }
}


(new Build())->test();
