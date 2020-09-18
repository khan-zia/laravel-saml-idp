<?xml version="1.0"?>
<md:EntityDescriptor xmlns:md="urn:oasis:names:tc:SAML:2.0:metadata" validUntil="2021-08-20T15:59:09Z" cacheDuration="PT1598371149S" entityID="{{ url(config('samlidp.issuer_uri')) }}>
  <md:IDPSSODescriptor WantAuthnRequestsSigned=" true" protocolSupportEnumeration="urn:oasis:names:tc:SAML:2.0:protocol">
  <md:KeyDescriptor use="signing">
    <ds:KeyInfo xmlns:ds="http://www.w3.org/2000/09/xmldsig#">
      <ds:X509Data>
        <ds:X509Certificate>{{ $cert }}</ds:X509Certificate>
      </ds:X509Data>
    </ds:KeyInfo>
  </md:KeyDescriptor>
  <md:KeyDescriptor use="encryption">
    <ds:KeyInfo xmlns:ds="http://www.w3.org/2000/09/xmldsig#">
      <ds:X509Data>
        <ds:X509Certificate>{{ $cert }}</ds:X509Certificate>
      </ds:X509Data>
    </ds:KeyInfo>
  </md:KeyDescriptor>
  <md:SingleLogoutService Binding="urn:oasis:names:tc:SAML:2.0:bindings:HTTP-Redirect" Location="{{ url(config('samlidp.slo_uri')) }}" />
  <md:NameIDFormat>urn:oasis:names:tc:SAML:2.0:nameid-format:persistent</md:NameIDFormat>
  <md:SingleSignOnService Binding="urn:oasis:names:tc:SAML:2.0:bindings:HTTP-Redirect" Location="{{ url(config('samlidp.sso_uri')) }}" />
  </md:IDPSSODescriptor>
  <md:Organization>
    <md:OrganizationName xml:lang=" en-US">Meveto Inc</md:OrganizationName>
    <md:OrganizationDisplayName xml:lang="en-US">Meveto</md:OrganizationDisplayName>
    <md:OrganizationURL xml:lang="en-US">https://meveto.com</md:OrganizationURL>
  </md:Organization>
  <md:ContactPerson contactType="technical">
    <md:GivenName>Zia U Rehman Khan</md:GivenName>
    <md:EmailAddress>zmajrohi323@gmail.com</md:EmailAddress>
  </md:ContactPerson>
  <md:ContactPerson contactType="support">
    <md:GivenName>Emir Ceric</md:GivenName>
    <md:EmailAddress>support@meveto.com</md:EmailAddress>
  </md:ContactPerson>
</md:EntityDescriptor>