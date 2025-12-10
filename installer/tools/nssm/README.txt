NSSM (Non-Sucking Service Manager) Integration

To enable automatic Windows Service installation during the NSIS installer run, place the appropriate `nssm.exe` binary for target architectures here before compiling the installer.

Steps:
1. Download NSSM from https://nssm.cc/download (choose correct architecture).
2. Place `nssm.exe` into this directory (you can create subfolders for x86/x64).
3. Run `makensis installer/nameless-installer.nsi` from the repository root; the installer will include the NSSM files and attempt to install a Windows Service named `NamelessPOS`.

If `nssm.exe` is not present in this directory, the installer will create a Scheduled Task fallback for auto-start.
