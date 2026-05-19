FROM eclipse-temurin:17-jdk AS build

WORKDIR /src
COPY src ./src
RUN javac -d /out src/main/java/com/msquad/testclients/App.java

FROM eclipse-temurin:17-jre

WORKDIR /app
COPY --from=build /out ./classes

ENTRYPOINT ["java", "-cp", "/app/classes", "com.msquad.testclients.App"]
