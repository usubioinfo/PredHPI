<#import "location.ftl" as locationMacro>
<#import "matchLocationPopup.ftl" as matchLocationPopupMacro>

<#macro matchLocation matchId proteinAc proteinLength signature location entryAc colourClass>
    <#assign title=signature.ac>
<#--Signatures like "G3DSA:3.20.20.80" cause issues, remove special characters -->
<#--TODO? Try http://stackoverflow.com/questions/296264/using-regular-expressions-in-jsp-el-->
    <#assign prefix=signature.ac?replace(":","")?replace(".","")>

    <#if standalone>
    <#--If InterProScan 5 HTML output-->
        <@locationMacro.location locationSpanId=prefix+"-span-"+matchId proteinLength=proteinLength titlePrefix=title location=location colourClass=colourClass/>
        <@matchLocationPopupMacro.matchLocationPopup matchPopupId=prefix+"-popup-"+matchId proteinAc=proteinAc signature=signature location=location colourClass=colourClass/>
    <#else>
    <#--If using this HTML in the InterPro website, get the hierarchy popup through an AJAX call-->
    <#--Integrated signature match or un-integrated signature, therefore has no entryAc associated-->
    <#--Gene3D or SUPERFAMILY where along with the signatureAc we also show the modelAc responsible for the hit-->
    <#--Sequence features where necessary (e.g. MobiDB)-->
    <a id="${prefix}-location-${matchId}"
       title="${title} ${location.start} - ${location.end}"
       class="match ${colourClass}"
       style="left:  ${(((location.start - 1) / proteinLength) * 100)?c}%;
               width: ${(((location.end - location.start + 1) / proteinLength) * 100)?c}%;"
       href="/interpro/popup/match?id=${prefix}-popup-${matchId}&proteinAc=${proteinAc}&methodAc=${signature.ac}&start=${location.start?c}&end=${location.end?c}<#if entryAc?? && entryAc?has_content && entryAc!="null">&entryAc=${entryAc}<#else>&db=${signature.dataSource.sourceName}</#if><#if location.models?? && location.models?has_content && location.models!="null" && signature.ac!=location.models>&model=${location.models}</#if><#if feature?? && feature?has_content && feature!="null">&feature=${feature}</#if>">
    </a>
    </#if>
</#macro>
