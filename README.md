# InDesign Server preview speed test
At the frontend the Studio web client can open a print article for editing and show a preview of 
how the article is placed on a layout. The preview is created with Adobe InDesign Server with help 
of the Studio plug-ins for InDesign. The Studio Server integrates with InDesign Server in the backend
and takes responsibility of creating a running InDesign Server jobs. The performance of the preview 
generation can be measured with help of this test tool.  

## Scenarios
The test tool simulates the requests fired by the Studio web client. First it creates a 
workspace and opens an article for editing and requests for a preview. Then it makes some changes 
to the article text and requests for an update of the preview. Then it saves the article in the 
file-store and requests for the preview again. 

## Use cases
For newspapers, magazines, books and brands, the use case can be rather different because of their
documents differ in dimensions and structure. There may be some or many pages having some or many
placed articles and/or images. Each article may consist of some or many components, frames and/or  
style definitions. Also editions may be used. All this may have impact on the performance and so 
some example documents are included to cover some use cases.

## Iterations
For a better average during performance measurement, the scenarios can be repeated for a given 
number of times. Advisable is to use at least 3 iterations. The performance of each iteration 
is simply measured individually. (The test tool does not calculate an average whatsoever.)

## Output in CSV
The performance of all these steps are measured and recorded in a CSV file. The CSV file can be 
opened e.g. in Excel or Numbers. How this CSV file is composed can be tuned with the options in 
the `idsspeedtest/config/config.php` file. 

## Installation
1. The `idsspeedtest` folder must be installed in the `server/wwtest` folder of Studio Server.
1. Start InDesign client and login to Studio Server you want to use for performance testing.
1. Open the layouts from the `idsspeedtest/input/<ids-version>/<use-case>` folders.
1. Save the layout in Studio Server.
1. Open the Articles panel and unfold the first article.
1. Select all its text frames and create an article in Studio Server.
1. For each graphic frame, create an image in Studio Server.
1. Repeat these steps for all articles listed in the Articles panel.
1. Check-in the layout.
1. Lookup the object `ID` for the layout and the first article.
1. Open the `idsspeedtest/input/usecases.json` file and fill in the `LayoutId` and `ArticleId` for the corresponding use case.
1. Repeat the steps above (from step 3 onwards) for each use case folder.
1. Save the JSON file.

## Execution
Make sure the `DEBUGLEVELS` option for Studio Server is set to `WARN` or `ERROR`. 
The test tool can be executed as follows to run all configured use cases for CC2017 repeated for 3 times:
```shell script
cd server/wwtest/idsspeedtest
PHP_EXE=php73 bash ids-speed-test.sh CC2017 3
```
Open the `idsspeedtest/reports/<ids-version>/ids-speed-test.csv` in e.g. Excel or Numbers.

## Future ideas
Some ideas to improve the tool:
- Make an InDesign script that automates the document creation in Studio Server. (Taking local documents from the `input` folder.)
- Make an InDesign Server script that creates layouts and articles from scratch. This to remove dependencies with InDesign versions.
- For a set of iterations, let the tool remove the best and worst performance and calculate the average. Only output the average to CSV.
