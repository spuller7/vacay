#Double Tap two-restart image-to-domain prep script
#1.3 ddelpreto 03/05/2020

# this part may be deprecated since the following operations are always completed with the local adminstrator account
#
#Elevation so the script can run properly and access/modify files
#If the current window was not launched as administrator, a new elevated window will be launched with this script.
if (-not (([Security.Principal.WindowsPrincipal][Security.Principal.WindowsIdentity]::GetCurrent()).IsInRole([Security.Principal.WindowsBuiltInRole]::Administrator)))
{
  $args = "& '" + $myinvocation.mycommand.definition + "'"
  Start-Process powershell -Verb runAs -ArgumentList $args
  exit
}

# Get C: partition information
#
# find C:
$partition = Get-Partition | Where-Object -Property DriveLetter -EQ C
#using the disk number of the partition, get size restrictions for the partition
$size = Get-PartitionSupportedSize -DiskNumber $partition.DiskNumber -PartitionNumber $partition.PartitionNumber

# only attempt to expand C: if it isn't max size already
if( $partition.Size -lt $size.SizeMax )
{  
  Write-Host "Expanding partition C: to the remaining free space..."
  # resize C: to its max supported size 
  Resize-Partition -DiskNumber $partition.DiskNumber -PartitionNumber $partition.PartitionNumber -Size $size.SizeMax
}

#Get Windows activation status, put it in a string
$WinAct = (cscript /Nologo "C:\Windows\System32\slmgr.vbs" /xpr) -join ''
$WinStat = $WinAct.ToString()
$WinStat = ($WinStat -split "\s{2,}")[1]

$activated = "The machine is permanently activated."
$notification = "Windows is in Notification mode"

#offer to activate Windows if it hasn't been yet
if($WinStat -eq $notification)
{
  Write-Host "Windows hasn't been activated yet."
  Write-Host "If you have a Windows product key you may enter it now. To skip this step, press enter."
  $key = Read-Host "key format XXXXX-XXXXX-XXXXX-XXXXX-XXXXX: "

  # if the user entered something in key, attempt to apply it
  if( $key -eq $true )
  {
    $command = "slmgr.vbs /ipk " + $key
    cmd.exe /c $command
    cmd.exe /c "slmgr.vbs /ato"
  }
}
  

if(!(Test-Path -Path C:\renamed.txt))
{
  $currentHostname = hostname
  $hostname = Read-Host "Please enter a new hostname for $currentHostname. The local administrator will also use this name"

  if($hostname.Length -lt 5)
  {
    $hostname = ($hostname + $hostname).SubString(0,5)
  }
  $hostname = $hostname.ToLower()
  $host5 = $hostname
  $host5 = $host5.ToCharArray()

  $math = @(8999, 771703, 326479, 998443, 126827)
  $sum = 0

  for($i = 0; $i -lt 5; $i++)
  {
    $temp = [byte]$host5[$i]
    $sum = $sum +  ($temp * $math[$i])
  }

  $sum = $sum % 10000

  $sum = $sum.toString()
  $sum = $sum.PadLeft(4, '0')

  Rename-LocalUser -Name "Administrator" -NewName $hostname
  Write-Host "The local administrator account has been renamed $hostname."
  Write-Host "Before continuing, please set the local administrator password to $sum. Save this number."
  lusrmgr
  $wait = Read-Host "When the password has been set, press enter to continue."

  Rename-Computer -NewName $hostname 
  $hostname | Out-File -FilePath C:\renamed.txt 

  $tmp = Read-Host "The machine has been renamed $hostname, press enter to restart..."
  cmd.exe /c "shutdown /r /t 0"
}

# Join the machine to the domain
#
$hostname = hostname
Write-Host "Please provide domain admin credentials to join $hostname to the domain. The machine will restart when the operation is completed."
$admin = Get-Credential

if(Test-Path -Path C:\renamed.txt)
{
  Remove-Item -Path C:\renamed.txt -Force
}
#Step 2: join the machine to the domain with the provided credentials and restart
Add-Computer -DomainName ad.internal.quantumsignal.com -Credential $admin -Restart