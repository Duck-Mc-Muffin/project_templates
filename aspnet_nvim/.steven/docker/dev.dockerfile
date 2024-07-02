FROM myproject_dev AS myproject_dev_nvim

# Neovim dependencies, missing in alpine linux
RUN --mount=type=cache,target=/var/cache/apk \
    apk add --update \
        git \
        clang \
        unzip \
        wget \
        curl \
        gzip \
        tar \
        bash \
        ripgrep \
        npm \
        fd

# Install neovim from source
RUN --mount=type=cache,target=/var/cache/apk \
    apk add --update \
        build-base \
        cmake \
        coreutils \
        gettext-tiny-dev \
    && mkdir /tmp/neovim

# Compile Neovim
# FIXME: The condition "command -v nvim" does not prevent recompilation as intended
RUN --mount=type=cache,target=/tmp/neovim \
    [ -d /tmp/neovim/source ] \
    || git clone --depth 1 --single-branch --branch nightly https://github.com/neovim/neovim /tmp/neovim/source \
    && command -v nvim &> /dev/null \
    || (cd /tmp/neovim/source \
    && make CMAKE_BUILD_TYPE=Release \
    && make install \
    && cd -)

# Configure Neovim from Github-repo
RUN mkdir /root/.config && git clone https://github.com/Duck-Mc-Muffin/nvim_config.git /root/.config/nvim

# Install plugins and LSPs
RUN nvim --headless "+Lazy! sync" +qa \
    && nvim --headless -c "MasonInstall csharp-language-server html-lsp typescript-language-server" -c "q"

# Helper for quality of life bash scripts
LABEL neovim=1

# Keep the container running to be able to restart the webserver
CMD ["sleep", "infinity"]
