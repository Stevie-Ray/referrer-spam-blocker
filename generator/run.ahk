/*
 * Author:   Thiago Talma (Brasil)
 * Homepage: http://thiagomt.com
 */
 
#Singleinstance, force
SetWorkingDir, %A_ScriptDir%
#NoEnv

HEADER_NGINX := A_ScriptDir "/templates/header_referral-spam.conf"
NGINX := A_ScriptDir "/../referral-spam.conf"

HEADER_APACHE := A_ScriptDir "/templates/header_htaccess"
FOOTER_APACHE := A_ScriptDir "/templates/footer_htaccess"
APACHE := A_ScriptDir "/../.htaccess"

ProcessTotal := 0
DomainList := Object()
FileRead, Domains, %A_ScriptDir%/domains.txt
if not ErrorLevel
{
    Sort, Domains
}

Loop, parse, Domains, `n, `r
{
    ProcessTotal += 1
    DomainList.Insert(A_LoopField)
    lastLine := A_LoopField
}
Domains =

ProcessTotal := ProcessTotal * 3

Progress, b w200 R0-%ProcessTotal%, A_Space, Generating...

FileDelete %NGINX%
FileDelete %APACHE% 

FormatTime, UPDATE, A_Now, yyyy-MM-dd

/*
Generate .htaccess
*/
FileRead, STR_APPEND, %HEADER_APACHE%
NCOR := "[NC,OR]"

Progress, , .htaccess

for index, domain in DomainList
{
    StepProcess++
    Progress, %StepProcess%

    IfEqual, domain, %lastLine%
        NCOR := "[NC]"
        
    STR_APPEND := STR_APPEND "RewriteCond %{HTTP_REFERER} ^http(s)?://(www\.)?.*" EscapeStr(domain) ".*$ " NCOR "`n"
}

STR_APPEND = %STR_APPEND%`nRewriteRule `^(.*)`$ – [F,L]`n`n
STR_APPEND = %STR_APPEND%</IfModule>`n`n
STR_APPEND = %STR_APPEND%<IfModule mod_setenvif.c>`n`n

for index, domain in DomainList
{
    StepProcess++
    Progress, %StepProcess%
    
    STR_APPEND := STR_APPEND "SetEnvIfNoCase Referer " domain " spambot=yes`n"
}

STR_APPEND := StrReplace(STR_APPEND, "##DATE##", UPDATE)

FileRead, FILE_APPEND, %FOOTER_APACHE%
STR_APPEND = %STR_APPEND%%FILE_APPEND%

FileAppend, %STR_APPEND%, %APACHE%
FILE_APPEND =

/*
Generate referral-spam.conf
*/
FileRead, STR_APPEND, %HEADER_NGINX%
Progress, , referral-spam.conf

for index, domain in DomainList
{
    StepProcess++
    Progress, %StepProcess%
    
    STR_APPEND := STR_APPEND Indent() """~*" EscapeStr(domain) """ 1`;`n"
}

STR_APPEND = %STR_APPEND%}
STR_APPEND := StrReplace(STR_APPEND, "##DATE##", UPDATE)
FileAppend, %STR_APPEND%, %NGINX%

Progress, Off
return

EscapeStr(str)
{    
    return StrReplace(str, ., "\.")
}

Indent()
{
    return A_Space A_Space A_Space A_Space
}