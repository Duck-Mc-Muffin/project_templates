# Docs: https://learn.microsoft.com/en-us/aspnet/core/host-and-deploy/docker/building-net-docker-images?view=aspnetcore-8.0#the-dockerfile
FROM mcr.microsoft.com/dotnet/sdk:8.0-alpine AS myproject_dev

# Source code location
WORKDIR /source

# Dependencies
# COPY *.csproj .
# RUN dotnet restore

# App
COPY ./.. .
# RUN dotnet publish --no-restore -o /app

# Production
# FROM mcr.microsoft.com/dotnet/aspnet:8.0-alpine EXPOSE 8080
# WORKDIR /app
# COPY --from=myproject_dev /app .
# ENTRYPOINT ["./aspnetapp"]
