<?xml version="1.0" encoding="UTF-8" ?>
<!--
ABES - creation le 14/02/2008

Exemple de conversion d'APOGEE vers TEF.
Le point de départ : informations APOGEE formatées selon un nommage des champs proposé par l'abes.
Le point d'arrivée : métadonnées incomplètes, qui respectent le noyau mini-TEF imposé à l'entrée de STAR (= respect de la phase Core du Schematron)

Sur les droits : au cours de la conversion, on aurait pu mettre par défaut les métadonnées de droit relatives à l'auteur. On ne le fait pas car on suppose ici
que ces méta sont saisies dans les formulaires STAR. C'est au choix.

 -->
<xsl:stylesheet
 xmlns:tef="http://www.abes.fr/abes/documents/tef"
 xmlns:local="http://www.local.univ.fr/theses"
 xmlns:dc="http://purl.org/dc/elements/1.1/"
 xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
 xmlns:dcterms="http://purl.org/dc/terms/"
 xmlns:mets="http://www.loc.gov/METS/"
 xmlns:metsRights="http://cosimo.stanford.edu/sdr/metsrights/"
 xmlns:xlink="http://www.w3.org/1999/xlink"
 xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
 version="2.0">
 <xsl:output method="xml" indent="yes" encoding="UTF-8"/>
 <!-- Paramètres fixés -->
 <xsl:param name="ETABLISSEMENT">NORM</xsl:param>
 <xsl:param name="autoriteSudoc_etabSoutenance">190906332</xsl:param>
 <!--<xsl:param name="LIBELLE_ETAB_SOUT"></xsl:param>-->
 <!-- Paramètres par défaut, éventuellement à modifier dans l'interface STAR -->
 <xsl:param name="langue_titre_defaut">fr</xsl:param>
 <xsl:param name="these_sur_travaux">non</xsl:param>
 <xsl:param name="langue_de_these">fr</xsl:param>

 <xsl:template match="/">
  <xsl:apply-templates select="//THESES" />
 </xsl:template>

<!-- Thèse -->
 <xsl:template match="THESE">
  <!-- -->
  <xsl:result-document href="{$ETABLISSEMENT}_{CODE_ETUDIANT}.xml">
  <!-- -->
   <mets:mets
    xsi:schemaLocation="http://www.loc.gov/METS/ http://www.abes.fr/abes/documents/tef/recommandation/tef_schemas.xsd"
    ID="{$ETABLISSEMENT}_{CODE_ETUDIANT}"
    OBJID="{CODE_ETUDIANT}"    >
   <!-- Création du bloc de md desciptives de la thèse -->
   <mets:dmdSec ID="{generate-id()}_desc_these">
    <mets:mdWrap MDTYPE="OTHER" OTHERMDTYPE="tef_desc_these">
     <mets:xmlData>
      <tef:thesisRecord>
       <dc:title xml:lang="{$langue_titre_defaut}">
        <xsl:value-of select="TITRE"/>
       </dc:title>
       <dc:type xsi:type="dcterms:DCMIType">Text</dc:type>
       <dc:type>Electronic Thesis or Dissertation</dc:type>
       <dc:language xsi:type="dcterms:RFC3066">
        <xsl:value-of select="$langue_de_these"></xsl:value-of>
       </dc:language>
      </tef:thesisRecord>
     </mets:xmlData>
    </mets:mdWrap>
   </mets:dmdSec>
 <!-- Pas de md descriptives de la version -->
 <!-- Pas de md descriptives de l'édition -->
 <!-- Création du bloc de md administratives de la thèse -->
   <mets:amdSec>
    <mets:techMD ID="{generate-id()}_admin">
     <mets:mdWrap MDTYPE="OTHER" OTHERMDTYPE="tef_admin_these">
      <mets:xmlData>
       <tef:thesisAdmin>
        <!-- tef:auteur -->
        <tef:auteur>
         <xsl:choose>
          <xsl:when test="NOM_ETUDIANT_USUEL != ''">
           <tef:nom>
            <xsl:value-of select="NOM_ETUDIANT_USUEL"/>
           </tef:nom>
           <tef:prenom>
            <xsl:value-of select="PRENOM_ETUDIANT"/>
           </tef:prenom>
           <tef:nomDeNaissance>
            <xsl:value-of select="NOM_ETUDIANT"/>
           </tef:nomDeNaissance>
          </xsl:when>
          <xsl:otherwise>
           <tef:nom>
            <xsl:value-of select="NOM_ETUDIANT"/>
           </tef:nom>
           <tef:prenom>
            <xsl:value-of select="PRENOM_ETUDIANT"/>
           </tef:prenom>
          </xsl:otherwise>
         </xsl:choose>
         <tef:dateNaissance>
          <xsl:call-template name="reformater_date">
           <xsl:with-param name="date" select="DATE_NAISSANCE_ETUDIANT" />
          </xsl:call-template>
         </tef:dateNaissance>
         <tef:autoriteExterne autoriteSource="CODE_ETUDIANT">
            <xsl:value-of select="CODE_ETUDIANT"/>
         </tef:autoriteExterne>
        </tef:auteur>
        <!-- Soutenance -->
        <dcterms:dateAccepted xsi:type="dcterms:W3CDTF">
         <xsl:call-template name="reformater_date">
          <xsl:with-param name="date" select="DATE_SOUTENANCE"/>
         </xsl:call-template>
        </dcterms:dateAccepted>
        <tef:thesis.degree>
         <tef:thesis.degree.discipline xml:lang="fr">
          <xsl:value-of select="DISCIPLINE"/>
         </tef:thesis.degree.discipline>
         <tef:thesis.degree.grantor>
          <tef:nom>
           <xsl:value-of select="LIBELLE_ETAB_SOUT"/>
          </tef:nom>
          <tef:autoriteExterne autoriteSource="RNE">
           <xsl:value-of select="CODE_ETAB_SOUT" />
          </tef:autoriteExterne>
          <tef:autoriteExterne autoriteSource="Sudoc">
           <xsl:value-of select="$autoriteSudoc_etabSoutenance"/>
          </tef:autoriteExterne>
         </tef:thesis.degree.grantor>
         <tef:thesis.degree.level>Doctorat</tef:thesis.degree.level>
        </tef:thesis.degree>
        <tef:theseSurTravaux>
         <xsl:value-of select="$these_sur_travaux"></xsl:value-of>
        </tef:theseSurTravaux>
        <tef:avisJury>oui</tef:avisJury>
        <!-- Directeur -->
        <tef:directeurThese>
         <tef:nom>
          <xsl:value-of select="NOM_DIRECTEUR"/>
         </tef:nom>
          <tef:prenom>
          <xsl:value-of select="PRENOM_DIRECTEUR"/>
          </tef:prenom>
        </tef:directeurThese>
        <!-- Ecoles doctorales -->
        <xsl:for-each select="*[contains(name(), 'LIBELLE_ECOLE_DOCTORALE')]">
         <tef:ecoleDoctorale>
          <tef:nom>
           <xsl:value-of select="."/>
          </tef:nom>
          <xsl:if test="preceding-sibling::*[contains(name(), 'CODE_ECOLE_DOCTORALE')]">
          <tef:autoriteExterne autoriteSource="Annuaire des formations doctorales et des unités de recherche">
           <xsl:value-of select="preceding-sibling::*[contains(name(), 'CODE_ECOLE_DOCTORALE')]"/>
          </tef:autoriteExterne>
          </xsl:if>
         </tef:ecoleDoctorale>
        </xsl:for-each>
        <!-- Equipes de recherche -->
        <xsl:for-each select="*[contains(name(), 'LIBELLE_EQUIPE_RECHERCHE')]">
         <tef:partenaireRecherche type="equipeRecherche">
          <tef:nom>
           <xsl:value-of select="."/>
          </tef:nom>
          <xsl:if test="preceding-sibling::*[contains(name(), 'CODE_EQUIPE_RECHERCHE')]">
           <tef:autoriteExterne autoriteSource=" Annuaire des formations doctorales et des unités de recherche">
            <xsl:value-of select="preceding-sibling::*[contains(name(), 'CODE_EQUIPE_RECHERCHE')]"/>
          </tef:autoriteExterne>
           </xsl:if>
        </tef:partenaireRecherche>
        </xsl:for-each>
       </tef:thesisAdmin>
      </mets:xmlData>
     </mets:mdWrap>
    </mets:techMD>
 <!--  Ce bloc explicite les actions qu'autorise le chef d'établissement. -->
    <mets:rightsMD ID="{generate-id()}_etab">
     <mets:mdWrap
      MDTYPE="OTHER"
      OTHERMDTYPE="tef_droits_etablissement_these"
      >
      <mets:xmlData>
       <metsRights:RightsDeclarationMD>
          <metsRights:Context CONTEXTCLASS="GENERAL PUBLIC">
           <metsRights:Permissions
            COPY="true" DISCOVER="true"
            DISPLAY="true" DUPLICATE="true" PRINT="true"
            MODIFY="false"  />
           <xsl:if test="DATE_FIN_CONF/text()">
            <metsRights:Constraints CONSTRAINTTYPE="TIME">
            <metsRights:ConstraintDescription>
             <xsl:text>confidentialité </xsl:text>
             <xsl:call-template name="reformater_date">
              <xsl:with-param name="date" select="DATE_SOUT"/>
             </xsl:call-template>
             <xsl:text> </xsl:text>
             <xsl:call-template name="reformater_date">
              <xsl:with-param name="date" select="DATE_FIN_CONF"/>
             </xsl:call-template>
            </metsRights:ConstraintDescription>
            </metsRights:Constraints>
           </xsl:if>
          </metsRights:Context>
       </metsRights:RightsDeclarationMD>
      </mets:xmlData>
     </mets:mdWrap>
    </mets:rightsMD>
   </mets:amdSec>
   <!--
  Génération de la carte de structure METS.

 Il existe une THESE, une VERSION_COMPLETE et une EDITION.
 Chacune de ces entités TEF est associée à un URI. Dans ce scénario, chaque URI est généré en partant de l'identifiant local de la thèse.
 L'identifiant local est inclus dans un URI géré par l'abes au sein du scheme URI info: (http://info-uri.info/registry/docs/misc/faq.html).
 Ainsi, l'identifiant local d'une thèse devient un identifiant global, unique à l'échelle du Web, un URI.
 Ce n'est qu'un scénario possible, à titre d'illustration !! La recommandation TEF ne se prononce pas sur la manière d'assigner un URI aux entités TEF.
 Ce choix relève de l'implémentation de la norme. Dans l'absolu, il serait possible d'utiliser l'identifiant pérenne (dc:identifier xsi:type="tef:nationalThesisPID")
 que l'abes assignera à chaque thèse, mais cet identifiant pérenne s'appuie sur le numéro national de thèse qui est connu tardivement, après la soutenance.
 Or, on peut vouloir échanger du TEF avant la soutenance (du TEF incomplet, certes...).

 info:fides/STAR/{$ETABLISSEMENT}/{$id} est l'URI de la thèse
 info:fides/STAR/{$ETABLISSEMENT}/{$id}/vc est l'URI de la la version complète (vc)
 info:fides/STAR/{$ETABLISSEMENT}/{$id}/vc/ed1 est l'URI d'une édition (ed1)

 info: est le schème URI (au même titre que urn, http...).
 fides est, dans cet exemple fictif, l'espace de noms de l'abes au sein de info.
 STAR est un sous-ensemble de l'espace fides (utilisable pour les échanges avec STAR).
 ISAL est le sous-ensemble de info:fides/STAR réservé à l'INSA de Lyon.
 {$id} contiendra l'id local de la thèse (valeur de l'attribut id, à la racine de la notice DC de départ)
-->
   <mets:structMap TYPE="logical">
    <mets:div
     TYPE="THESE"
     ADMID="{generate-id()}_admin {generate-id()}_etab"
     DMDID="{generate-id()}_desc_these"
     CONTENTIDS="info:fides/STAR/{$ETABLISSEMENT}/{CODE_ETUDIANT}">
    </mets:div>
   </mets:structMap>
  </mets:mets>
   <!-- -->
  </xsl:result-document>
  <!-- -->
 </xsl:template>
 <!-- Manipulation des dates -->
  <xsl:template name="reformater_date">
  <xsl:param name="date" />
  <xsl:value-of select="substring($date, 7, 4)"/>
  <xsl:text>-</xsl:text>
  <xsl:value-of select="substring($date, 4, 2)"/>
  <xsl:text>-</xsl:text>
  <xsl:value-of select="substring($date, 1, 2)"/>
 </xsl:template>
</xsl:stylesheet>
