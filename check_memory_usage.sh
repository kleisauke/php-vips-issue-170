#!/usr/bin/env bash
set -e
set -u

NEWLINE=$'\n'
result_summary=''
failure_count=0
test_iterations="${TEST_ITERATIONS:-10}"

echo "Starting container"
docker run --rm -p 8080:80 --shm-size=1gb --name=weserv weserv/images &
jpid="$!"

echo "Waiting for container to be ready"
sleep 3

echo ""
echo ""
echo "Proceeding to run tests"
echo ""

for (( i=1; $i <= $test_iterations; i++ )) ; do
  http_status=$(curl --write-out '%{http_code}' --silent --output /dev/null "http://127.0.0.1:8080/?url=https://github.com/ingenerator/libvips-segfault-repro/raw/master/html/photo.jpg&w=600&encoding=base64" || true)
  container_memory=$(docker stats --no-stream --format '{{.MemUsage}}' weserv)
  result_line="#$(printf '% 2d' "$i") HTTP:$http_status Memory: $container_memory"

  if [ $http_status != 200 ] ; then
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
final_memory=$(docker stats --no-stream --format '{{.MemUsage}}' weserv)
result_summary="$result_summary$NEWLINE  Final Memory: $final_memory"
echo ""
echo "Result summary:"
echo "$result_summary"
echo "$result_summary" > result-summary.txt
echo ""
echo "Total failures: $failure_count"

kill "$jpid"

if [ $failure_count != 0 ] ; then
  exit 1;
fi
