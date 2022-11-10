#!/bin/bash
set -o errexit
set -o nounset

NEWLINE=$'\n'
result_summary=''
failure_count=0
test_iterations="${TEST_ITERATIONS:-10}"

shutdown_behaviour="${VIPS_SHUTDOWN_BEHAVIOUR:-vips_shutdown}"
echo "Running tests with shutdown: $shutdown_behaviour"

echo "Starting container"
docker run --name=vips-test \
  --rm \
  -p8080:80 \
  vips-segfault-repro:latest &

echo "Waiting for container to be ready"
sleep 3

echo ""
echo ""
echo "Proceeding to run tests"
echo ""

for (( i=1; $i <= $test_iterations; i++ )) ; do
  http_status=$(curl --write-out '%{http_code}' --silent --output /dev/null "http://127.0.0.1:8080?shutdown_behaviour=$shutdown_behaviour" || true)
  container_memory=$(docker stats --no-stream --format '{{.MemUsage}}' 'vips-test')
  result_line="#$(printf '% 2d' "$i") HTTP:$http_status Memory: $container_memory"

  if [ http_status != 200 ] ; then
    failure_count=$((failure_count + 1))
  fi

  echo ""
  echo "RESULT: $result_line"
  echo "---"
  result_summary="$result_summary$NEWLINE  - $result_line"
done

echo ""
echo ""
echo "Waiting for final container memory to settle"
sleep 3
echo "Final memory usage: $(docker stats --no-stream --format '{{.MemUsage}}' vips-test)"
echo "Result summary:"
echo "$result_summary"
echo ""
echo "Total failures: $failure_count"

docker kill vips-test

if [ $failure_count != 0 ] ; then
  exit 1;
fi
