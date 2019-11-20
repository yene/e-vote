#!/bin/bash
rm -rf data
go build && ./e-vote data
