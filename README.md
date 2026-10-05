# Netherrack

**The networking layer for Minecraft server networks.**

Netherrack is a PHP-based networking and communication layer designed to connect Minecraft servers together through a unified proxy and network API.

It provides the foundation for building network-wide systems such as player transfers, ranks, permissions, clans, friends, server discovery, and other shared services without requiring every downstream server to implement its own networking logic.

> [!WARNING]
> **Netherrack is designed for trusted/private network environments.**
>
> Netherrack must **not** be deployed with downstream servers or internal communication endpoints exposed directly to the public Internet.
>
> If Netherrack and its downstream servers communicate across an untrusted network, then please wait for further updates for Upstream <-> Downstream Encryption.

---

## Why Netherrack?

Minecraft networks often end up with every server maintaining its own implementation of network-wide functionality.

Netherrack aims to change that.

Instead of:

```text
Lobby ── custom implementation
BedWars ── custom implementation
Factions ── custom implementation
SkyBlock ── custom implementation
```

Netherrack provides a common layer:

```text
                 Netherrack
                /     |     \
               /      |      \
           Lobby   BedWars   Factions
```

Servers can communicate with the network through a consistent interface while Netherrack handles the coordination between them.

---

## Features

Netherrack is built to support network-wide functionality

The exact functionality is intended to grow alongside the needs of the network.

---

## Architecture

A typical Netherrack network looks like:

```text
                     Players
                        │
                        ▼
                 ┌─────────────┐
                 │ Netherrack  │
                 │    Proxy    │
                 └──────┬──────┘
                        │
                 Network Communication
                        │
          ┌─────────────┼─────────────┐
          ▼             ▼             ▼
       Lobby         BedWars       Factions
```

Netherrack acts as the central communication layer between the proxy and downstream servers.

This allows individual servers to focus on their actual game functionality instead of independently implementing network infrastructure.

---

## Communication

Netherrack separates the **client-facing connection** from communication with downstream servers.

The connection between players and Netherrack is treated as an Internet-facing connection and uses encryption where required.

Communication between Netherrack and downstream servers is intended primarily for trusted infrastructure.

For servers running on the same machine, Netherrack can communicate through local interfaces without requiring application-level encryption for every packet.

For remote downstream servers, the communication channel isn't supported yet.

---

## Deployment

Netherrack is primarily intended to run in environments such as:

```text
                 VPS
                  │
          ┌───────▼───────┐
          │  Netherrack   │
          └───────┬───────┘
                  │
        ┌─────────┼─────────┐
        ▼         ▼         ▼
      Lobby    BedWars   Factions
```

The proxy should be the public entry point to the network.

Downstream servers should remain private and should not be independently accessible from the public Internet.

---

## Project Goals

Netherrack aims to provide:

- A unified networking layer
- A simple developer experience
- Consistent communication between servers
- Reusable network-wide functionality
- A clean PHP API
- Minimal duplication between downstream servers
- A foundation for larger Minecraft networks

The project is **not** intended to replace other Minecraft Server softwares.

Instead, Netherrack is a more simple layer and PHP-Based than others.

---

## Example

A game server can request a player transfer through Netherrack rather than implementing its own communication system:

```php
$player->getPlayerConnection()->transfer('lobby');
```

The exact API is subject to change during development.

The goal is to make network operations feel like normal application development rather than low-level packet management.

---

## Status

Netherrack is currently under active development.

APIs, protocols, and internal architecture may change significantly before the project reaches a stable release.

It should therefore be considered **experimental software** until a stable release is announced.

---

## Requirements

- PHP
- A compatible Minecraft server/proxy environment
- Network infrastructure capable of providing private communication between Netherrack and downstream servers

Additional requirements may vary depending on the features and integrations being used.

---

## Contributing

Contributions, ideas, and improvements are welcome.

When contributing, prioritize:

- Simple APIs
- Clear architecture
- Minimal unnecessary abstraction
- Compatibility
- Reliability
- Maintainability

Netherrack is intended to be infrastructure that developers can build on, not another layer of complexity that developers have to fight.

---

## License

See [`LICENSE`](LICENSE) for licensing information.
