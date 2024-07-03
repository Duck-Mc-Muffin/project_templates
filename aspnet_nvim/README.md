# Alpine .NET Core SDK + Neovim

This template describes how to build an ASP.NET-MVC alpine-image with the .NET Core SDK installed. This will serve as a local development container.

I will also describe how to extend that image with a working installation of [Neovim](https://neovim.io/) _inside_ the development container. Nothing except Docker needs to be present on the host system. No Neovim related configuration will be in version control or mentioned in `.gitignore`.

I also included notes about configuring Kestrel for self-signed certificates to use HTTPS locally.

All instances of `myproject` need to be replaced with an appropriate working title. Use `grep` to confirm:
```bash
grep --recursive myproject .
```

The folder `.steven/` is an example name for a personal folder and should be replaced. `.local/` is a good alternative, if it doesn't conflict with anything.

> SIDE NOTE:
> I like to keep Docker related files inside a `docker/` subdirectory, wich is why configuration files and commands look weird.

## Build

The the following command would spin up the container without Neovim.
```bash
docker compose --project-directory docker up --build -d
```

To include Neovim, the base image needs to be build first before building the extended image. Then the project's compose file needs to be extended as well, to spin up the new Neovim-image and its dependent volumes. To do this, create a symlink from `docker/compose.override.yml` to the Neovim override-file inside your personal folder. Then spin up your services as usual and docker will read the override-file automatically.
```bash
# Build the base image
docker build -f docker/Dockerfile -t myproject_dev .

# Symlik to the override-file
ln -s ../<personal_dir>/docker/compose.nvim.yml docker/compose.override.yml

# Spin up containers as usual
docker compose --project-directory docker up --build -d
```

Building the extended image might take a few minutes, since neovim will be compiled from source code.

## Initializing a project
You can create a project inside the container with the dotnet-cli:
```bash
docker exec -it <project_container> /bin/bash
dotnet new mvc -o /source
```

Inside the `Dockerfile`, remove the comment for this stage after the project has been initialized:
```Dockerfile
# Dependencies
COPY *.csproj .
RUN dotnet restore
```

## ASP.NET - HTTPS with redirect

### Installing the certificate

Docs: [Enforce HTTPS in ASP.NET Core](https://learn.microsoft.com/en-us/aspnet/core/security/enforcing-ssl#ubuntu-trust-the-certificate-for-service-to-service-communication)

We will create a bind volume to store the certificate first. Then we create it with a .NET tool _inside_ the container into the previously mounted directory.
Here is an example compose file:

```yaml
services:
  myproject-service:
    # ...
    ports:
      - 80:<container_http_port>
      - 443:<container_https_port>
    environment:
      # Configure Kestrel to use our certificate
      - ASPNETCORE_Kestrel__Certificates__Default__Password=<password_for_dev_certificate>
      - ASPNETCORE_Kestrel__Certificates__Default__Path=/root/.aspnet/https/aspnetapp.pfx
      # ...
    volumes:
      # Mount the Development certificate
      - type: bind 
        source: /path/to/your/certificate
        target: /root/.aspnet/https
      # ...
```

Restart the container, to create the volume. Then *switch into the container* and create a development certificate. The following command creates a self-signed certificate in the directory for wich we configured the volume:

```bash
dotnet dev-certs https -ep ${HOME}/.aspnet/https/aspnetapp.pfx -p <password_for_dev_certificate>
```

The certificate is now installed and persists inside the container. The last step is to instruct the host system to trust the certificate e. g. import it into your browser:
```bash
chrome://settings/certificates
```

> TODO: I have not figured out yet, how to properly install a certificate in Linux. Just importing it into a browser does not remove the warning.
### Configure HTTP-redirect

Here is an example for development in `Properties/launchSettings.json`. Make sure to use the right ports *and replace "localhost"* appropriatly. `0.0.0.0` accepts Requests form every IP-Address.
```json
  ...
  "profiles": {
    "https": {
      "commandName": "Project",
      "dotnetRunMessages": true,
      "launchBrowser": true,
      "applicationUrl": "https://0.0.0.0:<https_port>;http://0.0.0.0:<http_port>",
      "environmentVariables": {
        "ASPNETCORE_ENVIRONMENT": "Development"
      }
    },
    "http": {
      "commandName": "Project",
      "dotnetRunMessages": true,
      "launchBrowser": true,
      "applicationUrl": "http://0.0.0.0:<http_port>",
      "environmentVariables": {
        "ASPNETCORE_ENVIRONMENT": "Development"
      }
    },      
    ...
  }
  ...
```

> You can also move up the https-profile (shown above) to make it the "default" profile when executing `dotnet run`.

Change the redirect port in `Program.cs` to the mapped Docker port (the one reachable from "outside") and not the port the container uses (configured above):
```csharp

// Redirect to the correct https port (not the container port)
builder.Services.AddHttpsRedirection(options =>
{
    options.HttpsPort = 443;
});

```

## Additional info and tipps

The "personal folder" will be excluded from version control by adding it in `.git/info/exclude`. This avoids polluting `.gitignore` with "personal" configuration.

Some Neovim plugins require you to provide directories to persist plugin specific state.
In this case its handy to redirect this path into your personal folder inside the project, _if it exists_. Otherwise use a default directory.

I solved this by defining a helper function inside `init.lua`.
```lua
function GetPersonalDir()
    return vim.fs.find('<personal_dir>/nvim', {type = 'directory', upward = true})[1]
end
```

I use it to store the edit history ([undotree plugin](https://github.com/mbbill/undotree)) inside the project and otherwise in Neovim's standard data-dir.
```lua
-- If inside a "project" with a personal directory, use that directory for the undofile instead
local personal_dir = GetPersonalDir()
local undodir      = vim.fs.joinpath(vim.fn.stdpath('data'), 'undodir') -- default directory
if personal_dir ~= nil then
    undodir = vim.fs.joinpath(personal_dir, 'undodir')
end
```

To exclude these plugin files from telescope search, add a `.ignore` inside your personal directory to exclude e. g. the `nvim/` directory.

The SHADA file and some plugins persist their state in the XGD-directories (see `:help xdg`).
To not loose, for example, your Harpoon-hooks, create a bind volume into your personal folder (these should also be excluded from searches).
```yaml
volumes:
  - type: bind 
    source: ./../<personal_dir>/nvim/XDG_STATE_HOME
    target: /root/.local/state/nvim
  - type: bind 
    source: ./../<personal_dir>/nvim/XDG_DATA_HOME
    target: /root/.local/share/nvim
```
Create these directories in your personal folder, bevor building!

