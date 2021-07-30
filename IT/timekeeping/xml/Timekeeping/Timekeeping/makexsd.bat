testDate -N TimekeepingReqResp.cs TimekeepingReqResp.xsd
if errorlevel 1 goto skipTRR
%1\xsd /n:Timekeeping.TimekeepingReqResp /c TimekeepingReqResp.xsd
:skipTRR

exit /B 0