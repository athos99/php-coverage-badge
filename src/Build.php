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



    public function test()
    {
        $metric = $this->getCloverMetrics('clover.xml');
        //  var_dump($metric);

        $metric = $this->getCoberturaMetric('cobertura.xml');
        var_dump($metric);
    }
}


(new Build())->test();
