# Netherrack

**A PHP-based Minecraft Proxy Server Software.**

Netherrack is a Minecraft proxy designed to sit between Minecraft clients and downstream Minecraft servers, providing a central point for connection handling, routing, server switching, and communication across a Minecraft network.

Netherrack is designed to serve the same fundamental purpose as other Minecraft proxy software such as WDPE, while providing its own architecture, APIs, and implementation.

> [!WARNING]
> **Netherrack's current downstream transport is intended for trusted/private infrastructure.**
>
> Do not expose downstream endpoints directly to the public Internet with the current implementation.
>
> Upstream ↔ downstream encryption is planned for a future update.

---

## Architecture

A typical Netherrack deployment looks like:

```text
                    Minecraft Clients
                           │
                           │
                           ▼
                   ┌───────────────┐
                   │   Netherrack  │
                   │     Proxy     │
                   └───────┬───────┘
                           │
                    Downstream Transport
                           │
              ┌────────────┼────────────┐
              ▼            ▼            ▼
           Lobby        BedWars      Factions
           Server        Server       Server
```

Netherrack acts as the entry point for players and manages communication between clients and the servers behind the proxy.

---

## What Netherrack Does

Netherrack focuses on the core responsibilities of a Minecraft proxy.

### Client Connections

Netherrack accepts connections from Minecraft clients and handles the communication between the client and the proxy.

### Server Routing

Netherrack connects players to downstream servers and manages communication between the player and their current server.

### Server Switching

Players can be transferred between downstream servers without directly connecting to those servers themselves.

### Protocol Handling

Netherrack handles the communication and protocol requirements necessary to operate between Minecraft clients and downstream servers.

### Network Communication

Netherrack provides the communication layer required for the proxy to communicate with its downstream servers.

---

## Upstream & Downstream

Netherrack separates its communication into two primary directions.

```text
                UPSTREAM
Client ──────────────────────► Netherrack
                                  │
                                  │
                                  ▼
                              DOWNSTREAM
                              Netherrack
                                  │
                                  ▼
                              Server
```

### Upstream

The upstream connection is the connection between a Minecraft client and Netherrack.

This is the public-facing side of the proxy and is responsible for accepting player connections.

### Downstream

The downstream connection is the connection between Netherrack and the Minecraft server the player is currently connected to.

The current downstream transport is intended for trusted infrastructure.

---

## Encryption

Netherrack currently supports encryption on the client-facing side of the proxy.

The current architecture does **not** encrypt the downstream transport.

For a deployment where Netherrack and its downstream servers operate on the same machine or trusted private infrastructure, this avoids unnecessary cryptographic overhead and complexity.

Future versions of Netherrack will introduce **upstream ↔ downstream encryption** for deployments where additional transport security is required.

The goal is to make encrypted downstream communication an integrated part of Netherrack rather than requiring users to build their own solution around the proxy.

---

## Security

Netherrack assumes that downstream infrastructure is trusted in its current implementation.

A recommended deployment looks like:

```text
                         Internet
                            │
                            ▼
                    ┌──────────────┐
                    │  Netherrack  │
                    └──────┬───────┘
                           │
                    Private Network
                           │
              ┌────────────┼────────────┐
              ▼            ▼            ▼
           Lobby        BedWars      Factions
```

The proxy should be the public entry point to the network.

Downstream servers should **not** be independently exposed to the public Internet.

If downstream communication needs to cross an untrusted network, use an appropriate private/encrypted transport until native downstream encryption is available.

---

## Design Goals

Netherrack is built around a few straightforward goals:

- **Proxy-first architecture**
- **Reliable client connections**
- **Efficient server routing**
- **Clean upstream/downstream communication**
- **Simple configuration**
- **Extensible architecture**
- **Minimal unnecessary complexity**
- **Compatibility with Minecraft server networks**

Netherrack is intended to be infrastructure software.

It does not attempt to become a complete network-management platform.

---

## What Netherrack Is Not

Netherrack is **not** intended to provide systems such as:

- Ranks
- Clans
- Friends
- Economy
- Player databases
- Forums
- Website services
- Network management dashboards

Those systems can be built around a proxy, but they are outside the core responsibility of Netherrack.

Netherrack's job is to be the **proxy**.

---

## Example Network

A network using Netherrack could look like:

```text
                         Players
                            │
                            ▼
                    ┌───────────────┐
                    │   Netherrack  │
                    │     Proxy     │
                    └───────┬───────┘
                            │
          ┌─────────────────┼─────────────────┐
          │                 │                 │
          ▼                 ▼                 ▼
       Lobby            BedWars           Survival
       Server             Server            Server
```

Netherrack handles the proxy responsibilities while the downstream servers remain responsible for their own gameplay.

---

## Current Status

Netherrack is currently under active development.

The project is experimental and APIs, protocols, configuration formats, and internal components may change before a stable release.

### Roadmap

Planned development includes:

- Upstream ↔ downstream encryption
- Improved protocol support
- Improved server switching
- Additional proxy APIs
- Performance improvements
- Expanded configuration
- Stability improvements
- Production-ready releases

---

## Requirements

Netherrack is written in PHP.

Requirements may vary depending on the version and features being used.

The intended deployment environment is a Linux-based server capable of running the required PHP version and networking stack.

---

## Development

Netherrack is developed with a focus on keeping the proxy architecture understandable and maintainable.

The project aims to avoid unnecessary abstraction and complexity while still providing the components expected from modern Minecraft proxy software.

Contributions and improvements are welcome.

---

## License

See [`LICENSE`](LICENSE) for licensing information.
