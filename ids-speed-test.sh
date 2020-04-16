#!/bin/sh

set -e # bail out on error

: "${PHP_EXE:=php}" # Set default value to base php executable.

# Error when this script is not executed within the idsspeedtest folder.
currentPath=`pwd`
currentFolder=`basename ${currentPath}`
if [[ ! ${currentFolder} == "idsspeedtest" ]]; then
  echo "Error: Please run this script within the 'idsspeedtest' folder."
  exit 1
fi

# Validate the iterations argument.
if [ "$#" -ne 2 ]; then
    echo "Error: Illegal number of parameters. Please provide two arguments: the InDesign version and the number of iterations."
    exit 1
fi
idsVersion=${1}
echo "Preview scenarios will be profiled for all use cases configured for InDesign ${idsVersion}."
iterations=${2}
if [[ ! ${iterations} =~ ^[0-9]+$ ]]; then
    echo "Error: Provided iterations argument is not a number."
    exit 1
fi
if [ "${iterations}" -lt 1 ] || [ "${iterations}" -gt 5 ]; then
    echo "Error: Provided iterations argument should be in range of [1..5]."
    exit 1
fi
echo "Each use case will be iterated ${iterations} time(s)."

# Clean the server log folder.
logPath=`${PHP_EXE} getserverlogpath.php`
if [[ ! -n "${logPath}" ]]; then
  echo "Error: Can not determine server log folder."
  exit 1
fi
if [[ ! -d ${logPath} ]]; then
  echo "Error: Server log folder does not exist."
  exit 1
fi
profileFile="${logPath}/../sce_profile_mysql.htm"
echo "Cleaning server log folder: ${logPath}"
rm -rf ${logPath}/*
rm -f ${profileFile}

# Execute the IDS tests.
echo "Running tests for ${idsVersion}..."
# clear output from previous runs
rm -rf ./reports/${idsVersion}/*
# create report folder
mkdir -p ./reports/${idsVersion}
# init report with header row
csvReport="./reports/${idsVersion}/ids-speed-test.csv"
${PHP_EXE} ./ids-speed-test.php --idsversion=${idsVersion} --iteration=0 > ${csvReport}
# get the test scenarios to run
IFS=','
read -a testScenarios <<< "`${PHP_EXE} ./getusecases.php --idsversion=${idsVersion}`"
for testScenario in "${testScenarios[@]}"; do
  echo "Running ${idsVersion} > \"${testScenario}\" scenario..."
  for ((iteration=1; iteration <= ${iterations}; iteration++)); do
    echo "Running iteration #${iteration}..."
    # run test and add row to report
    ${PHP_EXE} ./ids-speed-test.php --idsversion=${idsVersion} --iteration=${iteration} --scenario="${testScenario}" >> ${csvReport}
    # move server logging and profiling to report folder
    iterationLogFolder="./reports/${idsVersion}/${testScenario}/iteration_${iteration}"
    mkdir -p "${iterationLogFolder}"
    mv ${logPath}/* "${iterationLogFolder}/" 2>/dev/null || :
    mv ${profileFile} "${iterationLogFolder}/" 2>/dev/null || :
  done
  echo "Created preview performance report: ${csvReport}"
  echo "Moved server logging and profiling to: ./reports/${idsVersion}/${testScenario}"
done
