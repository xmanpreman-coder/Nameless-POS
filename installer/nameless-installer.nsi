; Nameless.POS NSIS installer script
; Usage: makensis nameless-installer.nsi

!include "MUI2.nsh"

!define APP_NAME "Nameless.POS"
!define APP_EXE "NamelessPOS.exe" ; rename if your EXE has different name
!define APP_VERSION "1.0.0"

Name "${APP_NAME} ${APP_VERSION}"
OutFile "..\releases\${APP_NAME}_Setup_${APP_VERSION}.exe"
InstallDir "$PROGRAMFILES\\NamelessPOS"
RequestExecutionLevel admin

Page directory
Page instfiles
UninstPage uninstConfirm
UninstPage instfiles

Section "Install"
  SetOutPath "$INSTDIR"
  ; Copy all files from dist folder into installer package when compiled
  ; Ensure you run makensis from repo root so relative path works
  File /r "..\dist\*.*"

  ; Include NSSM files if provided in installer/tools/nssm
  ; Place nssm.exe into installer/tools/nssm before compiling to enable service install
  File /r "..\installer\tools\nssm\*.*"

  ; Create shortcuts
  CreateDirectory "$SMPROGRAMS\\${APP_NAME}"
  CreateShortCut "$SMPROGRAMS\\${APP_NAME}\\${APP_NAME}.lnk" "$INSTDIR\\${APP_EXE}"
  CreateShortCut "$DESKTOP\\${APP_NAME}.lnk" "$INSTDIR\\${APP_EXE}"

  ; Prefer NSSM-based Windows Service if nssm.exe was included; otherwise create Scheduled Task
  StrCpy $0 "$INSTDIR\\nssm\\nssm.exe"
  IfFileExists "$0" 0 +6
    ; Install service using NSSM
    ExecWait '"$INSTDIR\\nssm\\nssm.exe" install NamelessPOS """$INSTDIR\\${APP_EXE}"""'
    ExecWait '"$INSTDIR\\nssm\\nssm.exe" set NamelessPOS Start SERVICE_AUTO_START'
    ExecWait '"$INSTDIR\\nssm\\nssm.exe" start NamelessPOS'
    Goto +3

  ; Fallback: Create Scheduled Task to run at user logon (auto-start)
  StrCpy $1 "$INSTDIR\\${APP_EXE}"
  ExecWait 'schtasks /Create /SC ONLOGON /TN "NamelessPOS_AutoStart" /TR """$1""" /RL HIGHEST /F'

  ; Write install path to registry for uninstaller
  WriteRegStr HKLM "Software\${APP_NAME}" "Install_Dir" "$INSTDIR"

SectionEnd

Section "Uninstall"
  ; Stop any running app instances (best-effort)
  ; Try to kill by executable name
  ExecWait 'taskkill /IM "${APP_EXE}" /F' ; ignore errors

  ; If NSSM present, stop and remove service
  StrCpy $0 "$INSTDIR\\nssm\\nssm.exe"
  IfFileExists "$0" 0 +6
    ExecWait '"$INSTDIR\\nssm\\nssm.exe" stop NamelessPOS'
    ExecWait '"$INSTDIR\\nssm\\nssm.exe" remove NamelessPOS confirm'
    Goto +3

  ; Remove scheduled task
  ExecWait 'schtasks /Delete /TN "NamelessPOS_AutoStart" /F'

  ; Remove shortcuts
  Delete "$SMPROGRAMS\\${APP_NAME}\\${APP_NAME}.lnk"
  RMDir "$SMPROGRAMS\\${APP_NAME}"
  Delete "$DESKTOP\\${APP_NAME}.lnk"

  ; Remove files
  RMDir /r "$INSTDIR"

  ; Remove registry key
  DeleteRegKey HKLM "Software\${APP_NAME}"

SectionEnd
