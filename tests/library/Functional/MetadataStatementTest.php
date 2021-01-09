<?php

declare(strict_types=1);

/*
 * The MIT License (MIT)
 *
 * Copyright (c) 2014-2020 Spomky-Labs
 *
 * This software may be modified and distributed under the terms
 * of the MIT license.  See the LICENSE file for details.
 */

namespace Webauthn\Tests\Functional;

use Nyholm\Psr7\ServerRequest;
use Webauthn\PublicKeyCredentialCreationOptions;
use Webauthn\Tests\MemoryPublicKeyCredentialSourceRepository;

/**
 * @group functional
 * @group Fido2
 *
 * @internal
 */
final class MetadataStatementTest extends AbstractTestCase
{
    /**
     * @test
     * @dataProvider dataInvalidAttestation
     */
    public function theAttestationCannotBeVerified(string $options, string $response, string $message): void
    {
        //Then
        $this->expectExceptionMessage($message);

        //Given
        $request = new ServerRequest('POST', 'https://localhost/');
        $credentialRepository = new MemoryPublicKeyCredentialSourceRepository();
        $pkOptions = PublicKeyCredentialCreationOptions::createFromString($options);

        //When
        $publicKeyCredential = $this->getPublicKeyCredentialLoader()->load($response);
        $this->getAuthenticatorAttestationResponseValidator($credentialRepository)->check(
            $publicKeyCredential->getResponse(),
            $pkOptions,
            $request
        );
    }

    /**
     * @return array<int, array<int, string>>
     */
    public function dataInvalidAttestation(): array
    {
        return [
            [
                //F-1 Send a valid ServerAuthenticatorAttestationResponse with FULL "packed" attestation for metadata from MDS who's hash can not be verified, and check that serve returns an error
                '{"status":"ok","errorMessage":"","rp":{"name":"Webauthn Demo","id":"webauthn.spomky-labs.com"},"pubKeyCredParams":[{"type":"public-key","alg":-8},{"type":"public-key","alg":-7},{"type":"public-key","alg":-46},{"type":"public-key","alg":-35},{"type":"public-key","alg":-36},{"type":"public-key","alg":-257},{"type":"public-key","alg":-258},{"type":"public-key","alg":-259},{"type":"public-key","alg":-37},{"type":"public-key","alg":-38},{"type":"public-key","alg":-39}],"challenge":"Apx8UZ-48NK3KiEyECxI5wJOoWZ7Mh4V7wr8327wrjk","attestation":"direct","user":{"name":"gLUZSVCWWvdAO3Xjli_b","id":"N2QwY2U3MmItOGVlMi00OGYxLTgwMjAtODllOWU3YTIyZmEz","displayName":"Mozell Shue"},"authenticatorSelection":{"requireResidentKey":false,"userVerification":"preferred"},"timeout":60000}',
                '{"id":"t3IDC1YBBBNHrz5XYAsOROUm_XResfbn_xQ4Fdom_B8","rawId":"t3IDC1YBBBNHrz5XYAsOROUm_XResfbn_xQ4Fdom_B8","response":{"attestationObject":"o2NmbXRmcGFja2VkZ2F0dFN0bXSjY2FsZyZjc2lnWEcwRQIgV6NiE_2HKU4WDbBY8Lx_jDGBwcb_r8HvPku6cop1e3oCIQCTsCmsAvRYUwprVi_2rgDVDHn1FVxNocdLDmbp4GQdf2N4NWOBWQRFMIIEQTCCAimgAwIBAgIBATANBgkqhkiG9w0BAQsFADCBoTEYMBYGA1UEAwwPRklETzIgVEVTVCBST09UMTEwLwYJKoZIhvcNAQkBFiJjb25mb3JtYW5jZS10b29sc0BmaWRvYWxsaWFuY2Uub3JnMRYwFAYDVQQKDA1GSURPIEFsbGlhbmNlMQwwCgYDVQQLDANDV0cxCzAJBgNVBAYTAlVTMQswCQYDVQQIDAJNWTESMBAGA1UEBwwJV2FrZWZpZWxkMB4XDTE4MDUyMzE0Mzk0M1oXDTI4MDUyMDE0Mzk0M1owgcIxIzAhBgNVBAMMGkZJRE8yIEJBVENIIEtFWSBwcmltZTI1NnYxMTEwLwYJKoZIhvcNAQkBFiJjb25mb3JtYW5jZS10b29sc0BmaWRvYWxsaWFuY2Uub3JnMRYwFAYDVQQKDA1GSURPIEFsbGlhbmNlMSIwIAYDVQQLDBlBdXRoZW50aWNhdG9yIEF0dGVzdGF0aW9uMQswCQYDVQQGEwJVUzELMAkGA1UECAwCTVkxEjAQBgNVBAcMCVdha2VmaWVsZDBZMBMGByqGSM49AgEGCCqGSM49AwEHA0IABE86Xl6rbB-8rpf232RJlnYse-9yAEAqdsbyMPZVbxeqmZtZf8S_UIqvjp7wzQE_Wrm9J5FL8IBDeMvMsRuJtUajLDAqMAkGA1UdEwQCMAAwHQYDVR0OBBYEFFZN98D4xlW2oR9sTRnzv0Hi_QF5MA0GCSqGSIb3DQEBCwUAA4ICAQCH3aCf-CCJBdEtQc4JpOnUelwGGw7DxnBMokHHBgrzJxDn9BFcFwxGLxrFV7EfYehQNOD-74OS8fZRgZiNf9EDGAYiHh0-CspfBWd20zCIjlCdDBcyhwq3PLJ65JC_og3CT9AK4kvks4DI-01RYxNv9S8Jx1haO1lgU55hBIr1P_p21ZKnpcCEhPjB_cIFrHJqL5iJGfed-LXni9Suq24OHnp44Mrv4h7OD2elu5yWfdfFb-RGG2TYURFIGYGijsii093w0ZMBOfBS-3Xq_DrHeZbZrrNkY455gJCZ5eV83Nrt9J9_UF0VZHl_hwnSAUC_b3tN_l0ZlC9kPcNzJD04l4ndFBD2KdfQ2HGTX7pybWLZ7yH2BM3ui2OpiacaOzd7OE91rHYB2uZyQ7jdg25yF9M8QI9NHM_itCjdBvAYt4QCT8dX6gmZiIGR2F_YXZAsybtJ16pnUmODVbW80lPbzy-PUQYX79opeD9u6MBorzr9g08Elpb1F3DgSd8VSLlsR2QPllKl4AcJDMIOfZHOQGOzatMV7ipEVRa0L5FnjAWpHHvSNcsjD4Cul562mO3MlI2pCyo-US-nIzG5XZmOeu4Db_Kw_dEPOo2ztHwlU0qKJ7REBsbt63jdQtlwLuiLHwkpiwnrAOZfwbLLu9Yz4tL1eJlQffuwS_Aolsz7HGhhdXRoRGF0YViklgTqgoJOmKStoUtEYtDXOo7EaRMNqRsZMHRZIp90o1lBAAAAPYfGr_vnckVmtjJCRVDHR9wAILdyAwtWAQQTR68-V2ALDkTlJv10XrH25_8UOBXaJvwfpQECAyYgASFYIPv4RtIx7LBIv8RxVNQZNygHbexPcBQJb5QRY7CcaT3SIlgg2t1q-ymVz6Y9unzgLVGDxDHxgljdLqNS-OcsD9ccsE8","clientDataJSON":"eyJvcmlnaW4iOiJodHRwczovL3dlYmF1dGhuLnNwb21reS1sYWJzLmNvbSIsImNoYWxsZW5nZSI6IkFweDhVWi00OE5LM0tpRXlFQ3hJNXdKT29XWjdNaDRWN3dyODMyN3dyamsiLCJ0eXBlIjoid2ViYXV0aG4uY3JlYXRlIn0"},"type":"public-key"}',
                'The hash cannot be verified. The metadata statement shall be rejected',
            ],
            [
                //F-2 Send a valid ServerAuthenticatorAttestationResponse with FULL "packed" attestation for metadata from MDS who's status is set to USER_VERIFICATION_BYPASS, ATTESTATION_KEY_COMPROMISE, USER_KEY_REMOTE_COMPROMISE or USER_KEY_PHYSICAL_COMPROMISE, and check that serve returns an error
                '{"status":"ok","errorMessage":"","rp":{"name":"Webauthn Demo","id":"webauthn.spomky-labs.com"},"pubKeyCredParams":[{"type":"public-key","alg":-8},{"type":"public-key","alg":-7},{"type":"public-key","alg":-46},{"type":"public-key","alg":-35},{"type":"public-key","alg":-36},{"type":"public-key","alg":-257},{"type":"public-key","alg":-258},{"type":"public-key","alg":-259},{"type":"public-key","alg":-37},{"type":"public-key","alg":-38},{"type":"public-key","alg":-39}],"challenge":"8t3iLazERbCMgqkV_gua3zJLUiFZvXRDMpMTuaC6Zxk","attestation":"direct","user":{"name":"NR5n4h_aXaGfpKTJOanL","id":"ZTFmOGU3MWEtYjkwMS00MDQ0LWE0OGQtMTZiZmZjYjkyNTZk","displayName":"Lu Hopps"},"authenticatorSelection":{"requireResidentKey":false,"userVerification":"preferred"},"timeout":60000}',
                '{"id":"E6BLnfZCaZt5rCHpj528Mhw-mgO-ZpW8RyR3ImGnRY8","rawId":"E6BLnfZCaZt5rCHpj528Mhw-mgO-ZpW8RyR3ImGnRY8","response":{"attestationObject":"o2NmbXRmcGFja2VkZ2F0dFN0bXSjY2FsZyZjc2lnWEcwRQIhAL_v3vc2kXaFrKHgRCF4ZKLf3bnL3UDoOHK0dAXjvTThAiADPXM01IVUjMiayHjdYyhPuPbgFF74tTiNeMwlWSTABmN4NWOBWQRFMIIEQTCCAimgAwIBAgIBATANBgkqhkiG9w0BAQsFADCBoTEYMBYGA1UEAwwPRklETzIgVEVTVCBST09UMTEwLwYJKoZIhvcNAQkBFiJjb25mb3JtYW5jZS10b29sc0BmaWRvYWxsaWFuY2Uub3JnMRYwFAYDVQQKDA1GSURPIEFsbGlhbmNlMQwwCgYDVQQLDANDV0cxCzAJBgNVBAYTAlVTMQswCQYDVQQIDAJNWTESMBAGA1UEBwwJV2FrZWZpZWxkMB4XDTE4MDUyMzE0Mzk0M1oXDTI4MDUyMDE0Mzk0M1owgcIxIzAhBgNVBAMMGkZJRE8yIEJBVENIIEtFWSBwcmltZTI1NnYxMTEwLwYJKoZIhvcNAQkBFiJjb25mb3JtYW5jZS10b29sc0BmaWRvYWxsaWFuY2Uub3JnMRYwFAYDVQQKDA1GSURPIEFsbGlhbmNlMSIwIAYDVQQLDBlBdXRoZW50aWNhdG9yIEF0dGVzdGF0aW9uMQswCQYDVQQGEwJVUzELMAkGA1UECAwCTVkxEjAQBgNVBAcMCVdha2VmaWVsZDBZMBMGByqGSM49AgEGCCqGSM49AwEHA0IABE86Xl6rbB-8rpf232RJlnYse-9yAEAqdsbyMPZVbxeqmZtZf8S_UIqvjp7wzQE_Wrm9J5FL8IBDeMvMsRuJtUajLDAqMAkGA1UdEwQCMAAwHQYDVR0OBBYEFFZN98D4xlW2oR9sTRnzv0Hi_QF5MA0GCSqGSIb3DQEBCwUAA4ICAQCH3aCf-CCJBdEtQc4JpOnUelwGGw7DxnBMokHHBgrzJxDn9BFcFwxGLxrFV7EfYehQNOD-74OS8fZRgZiNf9EDGAYiHh0-CspfBWd20zCIjlCdDBcyhwq3PLJ65JC_og3CT9AK4kvks4DI-01RYxNv9S8Jx1haO1lgU55hBIr1P_p21ZKnpcCEhPjB_cIFrHJqL5iJGfed-LXni9Suq24OHnp44Mrv4h7OD2elu5yWfdfFb-RGG2TYURFIGYGijsii093w0ZMBOfBS-3Xq_DrHeZbZrrNkY455gJCZ5eV83Nrt9J9_UF0VZHl_hwnSAUC_b3tN_l0ZlC9kPcNzJD04l4ndFBD2KdfQ2HGTX7pybWLZ7yH2BM3ui2OpiacaOzd7OE91rHYB2uZyQ7jdg25yF9M8QI9NHM_itCjdBvAYt4QCT8dX6gmZiIGR2F_YXZAsybtJ16pnUmODVbW80lPbzy-PUQYX79opeD9u6MBorzr9g08Elpb1F3DgSd8VSLlsR2QPllKl4AcJDMIOfZHOQGOzatMV7ipEVRa0L5FnjAWpHHvSNcsjD4Cul562mO3MlI2pCyo-US-nIzG5XZmOeu4Db_Kw_dEPOo2ztHwlU0qKJ7REBsbt63jdQtlwLuiLHwkpiwnrAOZfwbLLu9Yz4tL1eJlQffuwS_Aolsz7HGhhdXRoRGF0YViklgTqgoJOmKStoUtEYtDXOo7EaRMNqRsZMHRZIp90o1lBAAAAP1vtdkch9k7Zm9szRbd0rEgAIBOgS532Qmmbeawh6Y-dvDIcPpoDvmaVvEckdyJhp0WPpQECAyYgASFYIMclewy6ydsFMZc2vZ0JSmcp3r2kf7UY6Q1JPF43fOlpIlggCreYKLBne0NVvFUOVeZxQq1AZECll_S4RGu9iw3pEJ8","clientDataJSON":"eyJvcmlnaW4iOiJodHRwczovL3dlYmF1dGhuLnNwb21reS1sYWJzLmNvbSIsImNoYWxsZW5nZSI6Ijh0M2lMYXpFUmJDTWdxa1ZfZ3VhM3pKTFVpRlp2WFJETXBNVHVhQzZaeGsiLCJ0eXBlIjoid2ViYXV0aG4uY3JlYXRlIn0"},"type":"public-key"}',
                'The authenticator is compromised and cannot be used',
            ],
            [
                //F-3 Send a valid ServerAuthenticatorAttestationResponse with FULL "packed" attestation for metadata from MDS who's signature can not be verified, and check that serve returns an error
                //We deactivated the MDS https://mds.certinfra.fidoalliance.org/execute/0fdcfc99b393efd496c165ee387e1f3949c3a16f85cfb0aa9c02bca979e75bdb
                // as it actually throws an error because of the signature
                '{"status":"ok","errorMessage":"","rp":{"name":"Webauthn Demo","id":"webauthn.spomky-labs.com"},"pubKeyCredParams":[{"type":"public-key","alg":-8},{"type":"public-key","alg":-7},{"type":"public-key","alg":-46},{"type":"public-key","alg":-35},{"type":"public-key","alg":-36},{"type":"public-key","alg":-257},{"type":"public-key","alg":-258},{"type":"public-key","alg":-259},{"type":"public-key","alg":-37},{"type":"public-key","alg":-38},{"type":"public-key","alg":-39}],"challenge":"bOJE3DykvKwSU9BrKVju9nfW3rwH4qMBk31bYBx7eWU","attestation":"direct","user":{"name":"acikp1tnBR4uK3e4Mj2W","id":"YzFjOGUwMDgtZmY2Yi00OTg2LWE3MjQtYjE3MjBjMjg2YjRj","displayName":"Taren Gatewood"},"authenticatorSelection":{"requireResidentKey":false,"userVerification":"preferred"},"timeout":60000}',
                '{"id":"LpGb0TGB_vsIXniDuNmkC6QGZBnBUmYKaJz2DdSTbbQ","rawId":"LpGb0TGB_vsIXniDuNmkC6QGZBnBUmYKaJz2DdSTbbQ","response":{"attestationObject":"o2NmbXRmcGFja2VkZ2F0dFN0bXSjY2FsZyZjc2lnWEcwRQIhANhlZvNRn-9KLNzCbtxAi90qXlMs_QcVENfU7FLuoEDzAiB3k9bR1vb-Z_7SQdH9Mgc17xsafLGHzR2hgkASumzT-WN4NWOBWQRFMIIEQTCCAimgAwIBAgIBATANBgkqhkiG9w0BAQsFADCBoTEYMBYGA1UEAwwPRklETzIgVEVTVCBST09UMTEwLwYJKoZIhvcNAQkBFiJjb25mb3JtYW5jZS10b29sc0BmaWRvYWxsaWFuY2Uub3JnMRYwFAYDVQQKDA1GSURPIEFsbGlhbmNlMQwwCgYDVQQLDANDV0cxCzAJBgNVBAYTAlVTMQswCQYDVQQIDAJNWTESMBAGA1UEBwwJV2FrZWZpZWxkMB4XDTE4MDUyMzE0Mzk0M1oXDTI4MDUyMDE0Mzk0M1owgcIxIzAhBgNVBAMMGkZJRE8yIEJBVENIIEtFWSBwcmltZTI1NnYxMTEwLwYJKoZIhvcNAQkBFiJjb25mb3JtYW5jZS10b29sc0BmaWRvYWxsaWFuY2Uub3JnMRYwFAYDVQQKDA1GSURPIEFsbGlhbmNlMSIwIAYDVQQLDBlBdXRoZW50aWNhdG9yIEF0dGVzdGF0aW9uMQswCQYDVQQGEwJVUzELMAkGA1UECAwCTVkxEjAQBgNVBAcMCVdha2VmaWVsZDBZMBMGByqGSM49AgEGCCqGSM49AwEHA0IABE86Xl6rbB-8rpf232RJlnYse-9yAEAqdsbyMPZVbxeqmZtZf8S_UIqvjp7wzQE_Wrm9J5FL8IBDeMvMsRuJtUajLDAqMAkGA1UdEwQCMAAwHQYDVR0OBBYEFFZN98D4xlW2oR9sTRnzv0Hi_QF5MA0GCSqGSIb3DQEBCwUAA4ICAQCH3aCf-CCJBdEtQc4JpOnUelwGGw7DxnBMokHHBgrzJxDn9BFcFwxGLxrFV7EfYehQNOD-74OS8fZRgZiNf9EDGAYiHh0-CspfBWd20zCIjlCdDBcyhwq3PLJ65JC_og3CT9AK4kvks4DI-01RYxNv9S8Jx1haO1lgU55hBIr1P_p21ZKnpcCEhPjB_cIFrHJqL5iJGfed-LXni9Suq24OHnp44Mrv4h7OD2elu5yWfdfFb-RGG2TYURFIGYGijsii093w0ZMBOfBS-3Xq_DrHeZbZrrNkY455gJCZ5eV83Nrt9J9_UF0VZHl_hwnSAUC_b3tN_l0ZlC9kPcNzJD04l4ndFBD2KdfQ2HGTX7pybWLZ7yH2BM3ui2OpiacaOzd7OE91rHYB2uZyQ7jdg25yF9M8QI9NHM_itCjdBvAYt4QCT8dX6gmZiIGR2F_YXZAsybtJ16pnUmODVbW80lPbzy-PUQYX79opeD9u6MBorzr9g08Elpb1F3DgSd8VSLlsR2QPllKl4AcJDMIOfZHOQGOzatMV7ipEVRa0L5FnjAWpHHvSNcsjD4Cul562mO3MlI2pCyo-US-nIzG5XZmOeu4Db_Kw_dEPOo2ztHwlU0qKJ7REBsbt63jdQtlwLuiLHwkpiwnrAOZfwbLLu9Yz4tL1eJlQffuwS_Aolsz7HGhhdXRoRGF0YViklgTqgoJOmKStoUtEYtDXOo7EaRMNqRsZMHRZIp90o1lBAAAAP9ImGBAv6EpVhWbSBveSpr4AIC6Rm9Exgf77CF54g7jZpAukBmQZwVJmCmic9g3Uk220pQECAyYgASFYIPk4lAzEzM3z7aiT5HhF6hxfmlfHa-75s9D3v1trb-MjIlggZneX4-qReISpwVbUua049L_l_18xuk4WImfiZnVH_CU","clientDataJSON":"eyJvcmlnaW4iOiJodHRwczovL3dlYmF1dGhuLnNwb21reS1sYWJzLmNvbSIsImNoYWxsZW5nZSI6ImJPSkUzRHlrdkt3U1U5QnJLVmp1OW5mVzNyd0g0cU1CazMxYllCeDdlV1UiLCJ0eXBlIjoid2ViYXV0aG4uY3JlYXRlIn0"},"type":"public-key"}',
                'The Metadata Statement for the AAGUID "d2261810-2fe8-4a55-8566-d206f792a6be" is missing',
            ],
            [
                //F-4 Send a valid ServerAuthenticatorAttestationResponse with FULL "packed" attestation for metadata from MDS who's certificate chain can not be verified, and check that serve returns an error
                '{"status":"ok","errorMessage":"","rp":{"name":"Webauthn Demo","id":"webauthn.spomky-labs.com"},"pubKeyCredParams":[{"type":"public-key","alg":-8},{"type":"public-key","alg":-7},{"type":"public-key","alg":-46},{"type":"public-key","alg":-35},{"type":"public-key","alg":-36},{"type":"public-key","alg":-257},{"type":"public-key","alg":-258},{"type":"public-key","alg":-259},{"type":"public-key","alg":-37},{"type":"public-key","alg":-38},{"type":"public-key","alg":-39}],"challenge":"jh4_akNWFT-CFm16Rv8Dvg8zq_dXWIIkgAbnArZH9s0","attestation":"direct","user":{"name":"pRjHi2nz6U0Vj6p3cvpw","id":"ZGFhNWIxNWMtNDQ1MS00NmNmLTlkMzItMWYxY2NkNzc4MzAx","displayName":"Shala Dull"},"authenticatorSelection":{"requireResidentKey":false,"userVerification":"preferred"},"timeout":60000}',
                '{"id":"g8J1PqurocrP_-oKxEeOk9BXOOL78pYqfn4JYcoGVfE","rawId":"g8J1PqurocrP_-oKxEeOk9BXOOL78pYqfn4JYcoGVfE","response":{"attestationObject":"o2NmbXRmcGFja2VkZ2F0dFN0bXSjY2FsZyZjc2lnWEcwRQIgfpvHF7p44T2-EvXGSvhFzb00le36fiYdWIfuLwefZdECIQC-UTohzNGX9g7gF7jk0ooNrtd9VRT8Gh_kKHW-PKd23GN4NWOBWQRFMIIEQTCCAimgAwIBAgIBATANBgkqhkiG9w0BAQsFADCBoTEYMBYGA1UEAwwPRklETzIgVEVTVCBST09UMTEwLwYJKoZIhvcNAQkBFiJjb25mb3JtYW5jZS10b29sc0BmaWRvYWxsaWFuY2Uub3JnMRYwFAYDVQQKDA1GSURPIEFsbGlhbmNlMQwwCgYDVQQLDANDV0cxCzAJBgNVBAYTAlVTMQswCQYDVQQIDAJNWTESMBAGA1UEBwwJV2FrZWZpZWxkMB4XDTE4MDUyMzE0Mzk0M1oXDTI4MDUyMDE0Mzk0M1owgcIxIzAhBgNVBAMMGkZJRE8yIEJBVENIIEtFWSBwcmltZTI1NnYxMTEwLwYJKoZIhvcNAQkBFiJjb25mb3JtYW5jZS10b29sc0BmaWRvYWxsaWFuY2Uub3JnMRYwFAYDVQQKDA1GSURPIEFsbGlhbmNlMSIwIAYDVQQLDBlBdXRoZW50aWNhdG9yIEF0dGVzdGF0aW9uMQswCQYDVQQGEwJVUzELMAkGA1UECAwCTVkxEjAQBgNVBAcMCVdha2VmaWVsZDBZMBMGByqGSM49AgEGCCqGSM49AwEHA0IABE86Xl6rbB-8rpf232RJlnYse-9yAEAqdsbyMPZVbxeqmZtZf8S_UIqvjp7wzQE_Wrm9J5FL8IBDeMvMsRuJtUajLDAqMAkGA1UdEwQCMAAwHQYDVR0OBBYEFFZN98D4xlW2oR9sTRnzv0Hi_QF5MA0GCSqGSIb3DQEBCwUAA4ICAQCH3aCf-CCJBdEtQc4JpOnUelwGGw7DxnBMokHHBgrzJxDn9BFcFwxGLxrFV7EfYehQNOD-74OS8fZRgZiNf9EDGAYiHh0-CspfBWd20zCIjlCdDBcyhwq3PLJ65JC_og3CT9AK4kvks4DI-01RYxNv9S8Jx1haO1lgU55hBIr1P_p21ZKnpcCEhPjB_cIFrHJqL5iJGfed-LXni9Suq24OHnp44Mrv4h7OD2elu5yWfdfFb-RGG2TYURFIGYGijsii093w0ZMBOfBS-3Xq_DrHeZbZrrNkY455gJCZ5eV83Nrt9J9_UF0VZHl_hwnSAUC_b3tN_l0ZlC9kPcNzJD04l4ndFBD2KdfQ2HGTX7pybWLZ7yH2BM3ui2OpiacaOzd7OE91rHYB2uZyQ7jdg25yF9M8QI9NHM_itCjdBvAYt4QCT8dX6gmZiIGR2F_YXZAsybtJ16pnUmODVbW80lPbzy-PUQYX79opeD9u6MBorzr9g08Elpb1F3DgSd8VSLlsR2QPllKl4AcJDMIOfZHOQGOzatMV7ipEVRa0L5FnjAWpHHvSNcsjD4Cul562mO3MlI2pCyo-US-nIzG5XZmOeu4Db_Kw_dEPOo2ztHwlU0qKJ7REBsbt63jdQtlwLuiLHwkpiwnrAOZfwbLLu9Yz4tL1eJlQffuwS_Aolsz7HGhhdXRoRGF0YViklgTqgoJOmKStoUtEYtDXOo7EaRMNqRsZMHRZIp90o1lBAAAAYEJEM_cnEEWSqryzmXlYsHkAIIPCdT6rq6HKz__qCsRHjpPQVzji-_KWKn5-CWHKBlXxpQECAyYgASFYIErKOqWLpKnqyJD-locpV3--N-LuYykHToo3gNxi1rGsIlggg-K9PpxUnI0H9Ji8axCsE-xZQeE8T8slmcdchv3vexM","clientDataJSON":"eyJvcmlnaW4iOiJodHRwczovL3dlYmF1dGhuLnNwb21reS1sYWJzLmNvbSIsImNoYWxsZW5nZSI6ImpoNF9ha05XRlQtQ0ZtMTZSdjhEdmc4enFfZFhXSUlrZ0FibkFyWkg5czAiLCJ0eXBlIjoid2ViYXV0aG4uY3JlYXRlIn0"},"type":"public-key"}',
                'Invalid certificate or certificate chain',
            ],
            [
                //F-5 Send a valid ServerAuthenticatorAttestationResponse with FULL "packed" attestation for metadata from MDS who's metadata service intermediate certificate is revoked, and check that serve returns an error
                '{"status":"ok","errorMessage":"","rp":{"name":"Webauthn Demo","id":"webauthn.spomky-labs.com"},"pubKeyCredParams":[{"type":"public-key","alg":-8},{"type":"public-key","alg":-7},{"type":"public-key","alg":-46},{"type":"public-key","alg":-35},{"type":"public-key","alg":-36},{"type":"public-key","alg":-257},{"type":"public-key","alg":-258},{"type":"public-key","alg":-259},{"type":"public-key","alg":-37},{"type":"public-key","alg":-38},{"type":"public-key","alg":-39}],"challenge":"4m1wvCHfmzTbe0M6Z_0DcPE44zF-ovSbuyXp83r9xno","attestation":"direct","user":{"name":"wn4mjR4Au6UO-f_ldcvu","id":"MDc0OTIyYTYtNzc0ZC00YTRmLThhYzMtOWVjNTI4MzcxZjll","displayName":"Lora Chasse"},"authenticatorSelection":{"requireResidentKey":false,"userVerification":"preferred"},"timeout":60000}',
                '{"id":"IU6CcyRAYUFOvz2vrZaZqEcKOg6q_hG9-CHhDs6zlmM","rawId":"IU6CcyRAYUFOvz2vrZaZqEcKOg6q_hG9-CHhDs6zlmM","response":{"attestationObject":"o2NmbXRmcGFja2VkZ2F0dFN0bXSjY2FsZyZjc2lnWEYwRAIgGzWK5epS57Nvrjm8N29Qdo43gGmn2v5i72aieL0oXRsCIBc_uPrdmziEcOLkXikz68m_xT993hiIegaYAMXMHZHrY3g1Y4FZBEUwggRBMIICKaADAgECAgEBMA0GCSqGSIb3DQEBCwUAMIGhMRgwFgYDVQQDDA9GSURPMiBURVNUIFJPT1QxMTAvBgkqhkiG9w0BCQEWImNvbmZvcm1hbmNlLXRvb2xzQGZpZG9hbGxpYW5jZS5vcmcxFjAUBgNVBAoMDUZJRE8gQWxsaWFuY2UxDDAKBgNVBAsMA0NXRzELMAkGA1UEBhMCVVMxCzAJBgNVBAgMAk1ZMRIwEAYDVQQHDAlXYWtlZmllbGQwHhcNMTgwNTIzMTQzOTQzWhcNMjgwNTIwMTQzOTQzWjCBwjEjMCEGA1UEAwwaRklETzIgQkFUQ0ggS0VZIHByaW1lMjU2djExMTAvBgkqhkiG9w0BCQEWImNvbmZvcm1hbmNlLXRvb2xzQGZpZG9hbGxpYW5jZS5vcmcxFjAUBgNVBAoMDUZJRE8gQWxsaWFuY2UxIjAgBgNVBAsMGUF1dGhlbnRpY2F0b3IgQXR0ZXN0YXRpb24xCzAJBgNVBAYTAlVTMQswCQYDVQQIDAJNWTESMBAGA1UEBwwJV2FrZWZpZWxkMFkwEwYHKoZIzj0CAQYIKoZIzj0DAQcDQgAETzpeXqtsH7yul_bfZEmWdix773IAQCp2xvIw9lVvF6qZm1l_xL9Qiq-OnvDNAT9aub0nkUvwgEN4y8yxG4m1RqMsMCowCQYDVR0TBAIwADAdBgNVHQ4EFgQUVk33wPjGVbahH2xNGfO_QeL9AXkwDQYJKoZIhvcNAQELBQADggIBAIfdoJ_4IIkF0S1Bzgmk6dR6XAYbDsPGcEyiQccGCvMnEOf0EVwXDEYvGsVXsR9h6FA04P7vg5Lx9lGBmI1_0QMYBiIeHT4Kyl8FZ3bTMIiOUJ0MFzKHCrc8snrkkL-iDcJP0AriS-SzgMj7TVFjE2_1LwnHWFo7WWBTnmEEivU_-nbVkqelwISE-MH9wgWscmovmIkZ9534teeL1K6rbg4eenjgyu_iHs4PZ6W7nJZ918Vv5EYbZNhREUgZgaKOyKLT3fDRkwE58FL7der8Osd5ltmus2RjjnmAkJnl5Xzc2u30n39QXRVkeX-HCdIBQL9ve03-XRmUL2Q9w3MkPTiXid0UEPYp19DYcZNfunJtYtnvIfYEze6LY6mJpxo7N3s4T3WsdgHa5nJDuN2DbnIX0zxAj00cz-K0KN0G8Bi3hAJPx1fqCZmIgZHYX9hdkCzJu0nXqmdSY4NVtbzSU9vPL49RBhfv2il4P27owGivOv2DTwSWlvUXcOBJ3xVIuWxHZA-WUqXgBwkMwg59kc5AY7Nq0xXuKkRVFrQvkWeMBakce9I1yyMPgK6XnraY7cyUjakLKj5RL6cjMbldmY567gNv8rD90Q86jbO0fCVTSoontEQGxu3reN1C2XAu6IsfCSmLCesA5l_Bssu71jPi0vV4mVB9-7BL8CiWzPscaGF1dGhEYXRhWKSWBOqCgk6YpK2hS0Ri0Nc6jsRpEw2pGxkwdFkin3SjWUEAAAAWO1DQSsRRTXa2I235OrnIhQAgIU6CcyRAYUFOvz2vrZaZqEcKOg6q_hG9-CHhDs6zlmOlAQIDJiABIVggeDWJ0O1pQEzguSKOcRdJQcxpyruPLmHRpA1Hf1RNP1ciWCBVZWYYYGUeL5OjUMagEMRsDpQR8bVxUM55o2C9NQM61g","clientDataJSON":"eyJvcmlnaW4iOiJodHRwczovL3dlYmF1dGhuLnNwb21reS1sYWJzLmNvbSIsImNoYWxsZW5nZSI6IjRtMXd2Q0hmbXpUYmUwTTZaXzBEY1BFNDR6Ri1vdlNidXlYcDgzcjl4bm8iLCJ0eXBlIjoid2ViYXV0aG4uY3JlYXRlIn0"},"type":"public-key"}',
                'Invalid certificate or certificate chain',
            ],
            [
                //F-6 Send a valid ServerAuthenticatorAttestationResponse with FULL "packed" attestation for metadata from MDS who's metadata service leaf certificate is revoked, and check that serve returns an error
                '{"status":"ok","errorMessage":"","rp":{"name":"Webauthn Demo","id":"webauthn.spomky-labs.com"},"pubKeyCredParams":[{"type":"public-key","alg":-8},{"type":"public-key","alg":-7},{"type":"public-key","alg":-46},{"type":"public-key","alg":-35},{"type":"public-key","alg":-36},{"type":"public-key","alg":-257},{"type":"public-key","alg":-258},{"type":"public-key","alg":-259},{"type":"public-key","alg":-37},{"type":"public-key","alg":-38},{"type":"public-key","alg":-39}],"challenge":"KyArg4Yu2BLZcJlSWd__zAgnIQDm3gqpBh1LWn12vrc","attestation":"direct","user":{"name":"CmweuTuRyMLBV8hz-H5k","id":"YzcyMWJkMjctYTU5My00NzI2LTgzMDctOGUwMjMzYTdlYzA2","displayName":"Eleanor Duchene"},"authenticatorSelection":{"requireResidentKey":false,"userVerification":"preferred"},"timeout":60000}',
                '{"id":"KeFtFgx3uneln_CsRy-M2KExIteZ5KdjNfB8qhHwEmw","rawId":"KeFtFgx3uneln_CsRy-M2KExIteZ5KdjNfB8qhHwEmw","response":{"attestationObject":"o2NmbXRmcGFja2VkZ2F0dFN0bXSjY2FsZyZjc2lnWEcwRQIhAJFBKEtT9e-QAibwTCSAdTTNAhO0RAXiEYeU4nkSd3XXAiAim5TiD4S7h1_OcVf5yB3XSZcfZRALk1EljV_F2kR4pmN4NWOBWQRFMIIEQTCCAimgAwIBAgIBATANBgkqhkiG9w0BAQsFADCBoTEYMBYGA1UEAwwPRklETzIgVEVTVCBST09UMTEwLwYJKoZIhvcNAQkBFiJjb25mb3JtYW5jZS10b29sc0BmaWRvYWxsaWFuY2Uub3JnMRYwFAYDVQQKDA1GSURPIEFsbGlhbmNlMQwwCgYDVQQLDANDV0cxCzAJBgNVBAYTAlVTMQswCQYDVQQIDAJNWTESMBAGA1UEBwwJV2FrZWZpZWxkMB4XDTE4MDUyMzE0Mzk0M1oXDTI4MDUyMDE0Mzk0M1owgcIxIzAhBgNVBAMMGkZJRE8yIEJBVENIIEtFWSBwcmltZTI1NnYxMTEwLwYJKoZIhvcNAQkBFiJjb25mb3JtYW5jZS10b29sc0BmaWRvYWxsaWFuY2Uub3JnMRYwFAYDVQQKDA1GSURPIEFsbGlhbmNlMSIwIAYDVQQLDBlBdXRoZW50aWNhdG9yIEF0dGVzdGF0aW9uMQswCQYDVQQGEwJVUzELMAkGA1UECAwCTVkxEjAQBgNVBAcMCVdha2VmaWVsZDBZMBMGByqGSM49AgEGCCqGSM49AwEHA0IABE86Xl6rbB-8rpf232RJlnYse-9yAEAqdsbyMPZVbxeqmZtZf8S_UIqvjp7wzQE_Wrm9J5FL8IBDeMvMsRuJtUajLDAqMAkGA1UdEwQCMAAwHQYDVR0OBBYEFFZN98D4xlW2oR9sTRnzv0Hi_QF5MA0GCSqGSIb3DQEBCwUAA4ICAQCH3aCf-CCJBdEtQc4JpOnUelwGGw7DxnBMokHHBgrzJxDn9BFcFwxGLxrFV7EfYehQNOD-74OS8fZRgZiNf9EDGAYiHh0-CspfBWd20zCIjlCdDBcyhwq3PLJ65JC_og3CT9AK4kvks4DI-01RYxNv9S8Jx1haO1lgU55hBIr1P_p21ZKnpcCEhPjB_cIFrHJqL5iJGfed-LXni9Suq24OHnp44Mrv4h7OD2elu5yWfdfFb-RGG2TYURFIGYGijsii093w0ZMBOfBS-3Xq_DrHeZbZrrNkY455gJCZ5eV83Nrt9J9_UF0VZHl_hwnSAUC_b3tN_l0ZlC9kPcNzJD04l4ndFBD2KdfQ2HGTX7pybWLZ7yH2BM3ui2OpiacaOzd7OE91rHYB2uZyQ7jdg25yF9M8QI9NHM_itCjdBvAYt4QCT8dX6gmZiIGR2F_YXZAsybtJ16pnUmODVbW80lPbzy-PUQYX79opeD9u6MBorzr9g08Elpb1F3DgSd8VSLlsR2QPllKl4AcJDMIOfZHOQGOzatMV7ipEVRa0L5FnjAWpHHvSNcsjD4Cul562mO3MlI2pCyo-US-nIzG5XZmOeu4Db_Kw_dEPOo2ztHwlU0qKJ7REBsbt63jdQtlwLuiLHwkpiwnrAOZfwbLLu9Yz4tL1eJlQffuwS_Aolsz7HGhhdXRoRGF0YViklgTqgoJOmKStoUtEYtDXOo7EaRMNqRsZMHRZIp90o1lBAAAAh_9VWdSGGUSPj9EvtQSpDW0AICnhbRYMd7p3pZ_wrEcvjNihMSLXmeSnYzXwfKoR8BJspQECAyYgASFYIC3d6UxmLxcfSYxb98_gz6LSeCHAA-MdVe89WiT6RCFXIlggP2qNj36ZcGhwCSUfaGL_Qf8Qr9HyD338ftvs5HNHdzA","clientDataJSON":"eyJvcmlnaW4iOiJodHRwczovL3dlYmF1dGhuLnNwb21reS1sYWJzLmNvbSIsImNoYWxsZW5nZSI6Ikt5QXJnNFl1MkJMWmNKbFNXZF9fekFnbklRRG0zZ3FwQmgxTFduMTJ2cmMiLCJ0eXBlIjoid2ViYXV0aG4uY3JlYXRlIn0"},"type":"public-key"}',
                'Invalid certificate or certificate chain',
            ],
        ];
    }
}
