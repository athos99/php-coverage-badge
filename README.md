
[![Quality gate](https://sonarcloud.io/api/project_badges/quality_gate?project=athos99_php-coverage-badge)](https://sonarcloud.io/summary/new_code?id=athos99_php-coverage-badge)

# php-coverage-badge

PHP-Coverage-Badge is a GitAction for creating SVG coverage badges. It parses XML report files such as Clover or Corbertura 
and creates badges based on the coverage of lines and branches of the code.

## This is the type of badge generated

![badge](https://raw.githubusercontent.com/athos99/php-coverage-badge/refs/heads/main/test/clover_coverage_line.svg)
![badge](https://raw.githubusercontent.com/athos99/php-coverage-badge/refs/heads/main/test/clover_coverage_branch.svg)
![badge](https://raw.githubusercontent.com/athos99/php-coverage-badge/refs/heads/main/test/cobertura_coverage_line.svg)
![badge](https://raw.githubusercontent.com/athos99/php-coverage-badge/refs/heads/main/test/cobertura_coverage_branch.svg)



## How to use in you gtiaction yml file

```
   - name: clover report
        uses: athos99/php-coverage-badge@v1
        with:
          report: 'coverage/clover.xml'
          report_type: 'clover'
          coverage_line_badge_path: 'output/coverage_line.svg'
          coverage_branch_badge_path: 'output/coverage_branch.svg'

```          

## Archiving badges in Git

Generated badges are not archived by default in your Git repository. You must archive them.


```
    - name: archive
        run: git config --local user.name actions-user
      - run: git config --local user.email "actions@github.com" 
      - run: git add out/coverage_line.svg
      - run: git add out/coverage_branch.svg
      - run: 'git commit -m "chore: add coverage badges"  || true'
        
      - name: Push changes # push the output folder to your repo
        uses: ad-m/github-push-action@master
        with:
          github_token: ${{ secrets.GITHUB_TOKEN }}
          force: true
```

You can take inspiration from the *.github/workflows/test.yml* file to integrate php-covevrage-badge into your GitAction


## Inputs

Paths are always relative to the root of your repository.

- report:
   - description: 'The path to the report file.'
   - required: false
   - default: 'clover.xml'

-  report_type:
   - description: 'The type of the generated report. Currently supported: clover, cobertura.'
   - required: false
   - default: 'clover'

-  coverage_line_badge_name:
   - description: 'The name of the line coverage badge'
   - required: false
   - default: 'Line coverage'

-  coverage_branch_badge_name:
   - description: 'The name of the branch coverage badge'
   - required: false
   - default: 'Branch coverage'

  - coverage_line_badge_path:
    - description: 'The path of the line coverage badge'
    - required: false
    - default: 'coverage_line.svg'

  - coverage_branch_badge_path:
    - description: 'The path of the branch coverage badge'
    - required: false
    - default: 'coverage_breanche.svg'

  - coverage_line_percent_ok:
    - description: 'Acceptable limit of coverage line percent'
    - required: false
    - default: '80'

  - coverage_branch_percent_ok:
    - description: 'Acceptable limit of coverage barnch percent'
    - required: false
    - default: '60'

  

Based on https://github.com/cicirello/jacoco-badge-generator
