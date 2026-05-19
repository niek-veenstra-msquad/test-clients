FROM golang:1.24-alpine AS build

WORKDIR /src
COPY go.mod main.go ./
RUN go build -o /out/ping-pong-client .

FROM alpine:3.21

WORKDIR /app
COPY --from=build /out/ping-pong-client ./

CMD ["./ping-pong-client"]
