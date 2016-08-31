<cartridge_basiclti_link
  xmlns="http://www.imsglobal.org/xsd/imslticc_v1p0"
  xmlns:blti="http://www.imsglobal.org/xsd/imsbasiclti_v1p0"
  xmlns:lticm="http://www.imsglobal.org/xsd/imslticm_v1p0"
  xmlns:lticp="http://www.imsglobal.org/xsd/imslticp_v1p0"
  xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:schemalocation="http://www.imsglobal.org/xsd/imslticc_v1p0 http://www.imsglobal.org/xsd/lti/ltiv1p0/imslticc_v1p0.xsd http://www.imsglobal.org/xsd/imsbasiclti_v1p0 http://www.imsglobal.org/xsd/lti/ltiv1p0/imsbasiclti_v1p0p1.xsd http://www.imsglobal.org/xsd/imslticm_v1p0 http://www.imsglobal.org/xsd/lti/ltiv1p0/imslticm_v1p0.xsd http://www.imsglobal.org/xsd/imslticp_v1p0 http://www.imsglobal.org/xsd/lti/ltiv1p0/imslticp_v1p0.xsd">
  <blti:title>Rubric Scores</blti:title>
  <blti:description>
    <!--[CDATA[Export rubric scores for an assignment]]-->
  </blti:description>
  <blti:launch_url>https://apps.ats.udel.edu/canvas/export_rubric_scores/shared.php</blti:launch_url>
  <blti:custom>
    <lticm:property name="input"><?php echo $_GET['input'] ?></lticm:property>
  </blti:custom>
  <blti:extensions platform="canvas.instructure.com">
    <lticm:property name="tool_id">export_rubric_scores_option</lticm:property>
    <lticm:property name="privacy_level">public</lticm:property>
    <lticm:options name="course_navigation">
      <lticm:property name="enabled">false</lticm:property>
      <lticm:property name="url">https://apps.ats.udel.edu/canvas/export_rubric_scores/shared.php</lticm:property>
      <lticm:property name="visibility">admins</lticm:property>
    </lticm:options>
  </blti:extensions>
</cartridge_basiclti_link>
    </blti:extensions>
    <cartridge_bundle identifierref="BLTI001_Bundle"/>
    <cartridge_icon identifierref="BLTI001_Icon"/>
</cartridge_basiclti_link>     