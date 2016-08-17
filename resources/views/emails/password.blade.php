@extends('layouts.emails.main')
@section('content')
<table style="border-spacing: 0;border-collapse: collapse;vertical-align: top;background-color: #F5F5F3" cellpadding="0" cellspacing="0" align="center" width="100%" border="0">
    <tbody>
        <tr style="vertical-align: top">
            <td style="word-break: break-word;border-collapse: collapse !important;vertical-align: top" width="100%">
                <!--[if gte mso 9]>
                <table id="outlookholder" border="0" cellspacing="0" cellpadding="0" align="center">
                    <tr>
                        <td>
                            <![endif]-->
                            <!--[if (IE)]>
                            <table width="500" align="center" cellpadding="0" cellspacing="0" border="0">
                                <tr>
                                    <td>
                                        <![endif]-->
                                        <table class="container" style="border-spacing: 0;border-collapse: collapse;vertical-align: top;max-width: 500px;margin: 0 auto;text-align: inherit" cellpadding="0" cellspacing="0" align="center" width="100%" border="0">
                                            <tbody>
                                                <tr style="vertical-align: top">
                                                    <td style="word-break: break-word;border-collapse: collapse !important;vertical-align: top" width="100%">
                                                        <table class="block-grid" style="border-spacing: 0;border-collapse: collapse;vertical-align: top;width: 100%;max-width: 500px;color: #000000;background-color: transparent" cellpadding="0" cellspacing="0" width="100%" bgcolor="transparent">
                                                            <tbody>
                                                                <tr style="vertical-align: top">
                                                                    <td style="word-break: break-word;border-collapse: collapse !important;vertical-align: top;text-align: center;font-size: 0">
                                                                        <!--[if (gte mso 9)|(IE)]>
                                                                        <table width="100%" align="center" bgcolor="transparent" cellpadding="0" cellspacing="0" border="0">
                                                                            <tr>
                                                                                <![endif]--><!--[if (gte mso 9)|(IE)]>
                                                                                <td valign="top" width="500">
                                                                                    <![endif]-->
                                                                                    <div class="col num12" style="display: inline-block;vertical-align: top;width: 100%">
                                                                                        <table style="border-spacing: 0;border-collapse: collapse;vertical-align: top" cellpadding="0" cellspacing="0" align="center" width="100%" border="0">
                                                                                            <tbody>
                                                                                                <tr style="vertical-align: top">
                                                                                                    <td style="word-break: break-word;border-collapse: collapse !important;vertical-align: top;background-color: transparent;padding-top: 5px;padding-right: 0px;padding-bottom: 5px;padding-left: 0px;border-top: 0px solid transparent;border-right: 0px solid transparent;border-bottom: 0px solid transparent;border-left: 0px solid transparent">
                                                                                                        <table style="border-spacing: 0;border-collapse: collapse;vertical-align: top" cellpadding="0" cellspacing="0" width="100%">
                                                                                                            <tbody>
                                                                                                                <tr style="vertical-align: top">
                                                                                                                    <td style="word-break: break-word;border-collapse: collapse !important;vertical-align: top;padding-top: 10px;padding-right: 10px;padding-bottom: 10px;padding-left: 10px">
                                                                                                                        <div style="color:#555555;line-height:120%;font-family:Arial, 'Helvetica Neue', Helvetica, sans-serif;">
                                                                                                                            <div style='font-size:12px;line-height:14px;text-align:center;font-family:Arial, "Helvetica Neue", Helvetica, sans-serif;color:#555555;'>
                                                                                                                                <p style="margin: 0;font-size: 14px;line-height: 17px;text-align: center"><span style="font-size: 14px; line-height: 16px;">Hi {{ $user->full_name }},</span></p>
                                                                                                                                <p style="margin: 0;font-size: 14px;line-height: 16px;text-align: center"><span style="font-size: 14px; line-height: 16px;">&nbsp;</span>&nbsp;<br></p>
                                                                                                                                <span style="font-size: 14px; line-height: 16px;" mce-data-marked="1">Sorry that you misplaced your password. It happens</span><span style="font-size: 14px; line-height: 16px;">. Click below to reset it.&nbsp;</span>
                                                                                                                                <p style="margin: 0;font-size: 14px;line-height: 16px;text-align: center">&nbsp;<br></p>
                                                                                                                            </div>
                                                                                                                        </div>
                                                                                                                    </td>
                                                                                                                </tr>
                                                                                                            </tbody>
                                                                                                        </table>
                                                                                                        <table style="border-spacing: 0;border-collapse: collapse;vertical-align: top" width="100%" border="0" cellspacing="0" cellpadding="0">
                                                                                                            <tbody>
                                                                                                                <tr style="vertical-align: top">
                                                                                                                    <td class="button-container" style="word-break: break-word;border-collapse: collapse !important;vertical-align: top;padding-top: 10px;padding-right: 10px;padding-bottom: 10px;padding-left: 10px" align="center">
                                                                                                                        <table style="border-spacing: 0;border-collapse: collapse;vertical-align: top" width="100%" border="0" cellspacing="0" cellpadding="0" align="center">
                                                                                                                            <tbody>
                                                                                                                                <tr style="vertical-align: top">
                                                                                                                                    <td class="button" style="word-break: break-word;border-collapse: collapse !important;vertical-align: top" width="100%" align="center" valign="middle">
                                                                                                                                        <!--[if mso]>
                                                                                                                                        <v:roundrect xmlns:v="urn:schemas-microsoft-com:vml" xmlns:w="urn:schemas-microsoft-com:office:word" href="{{ frontend_url('password/recovery', $token) }}"
                                                                                                                                            style="
                                                                                                                                            height:38px;
                                                                                                                                            v-text-anchor:middle;
                                                                                                                                            width:226px;"
                                                                                                                                            arcsize="0%"
                                                                                                                                            strokecolor="#0B2639"
                                                                                                                                            fillcolor="#0B2639" >
                                                                                                                                            <w:anchorlock/>
                                                                                                                                            <center 
                                                                                                                                                style="color:#00CFFF;
                                                                                                                                                font-family:'Open Sans', 'Helvetica Neue', Helvetica, Arial, sans-serif;
                                                                                                                                                font-size:14px;">
                                                                                                                                                <![endif]-->
                                                                                                                                                <!--[if !mso]><!- - -->
                                                                                                                                                <div style="display: inline-block;
                                                                                                                                                    border-radius: 0px; 
                                                                                                                                                    -webkit-border-radius: 0px; 
                                                                                                                                                    -moz-border-radius: 0px; 
                                                                                                                                                    max-width: 100%;
                                                                                                                                                    width: auto;
                                                                                                                                                    border-top: 0px solid transparent;
                                                                                                                                                    border-right: 0px solid transparent;
                                                                                                                                                    border-bottom: 0px solid transparent;
                                                                                                                                                    border-left: 0px solid transparent;" align="center">
                                                                                                                                                    <table style="border-spacing: 0;border-collapse: collapse;vertical-align: top;height: 38" width="100%" border="0" cellspacing="0" cellpadding="0">
                                                                                                                                                        <tbody>
                                                                                                                                                            <tr style="vertical-align: top">
                                                                                                                                                                <td style="word-break: break-word;border-collapse: collapse !important;vertical-align: top;border-radius: 0px;                   -webkit-border-radius: 0px;                   -moz-border-radius: 0px;                  color: #00CFFF;                  background-color: #0B2639;                  padding-top: 5px;                   padding-right: 20px;                  padding-bottom: 5px;                  padding-left: 20px;                  font-family: 'Open Sans', 'Helvetica Neue', Helvetica, Arial, sans-serif;text-align: center" valign="middle">
                                                                                                                                                                    <!--<![endif]-->
                                                                                                                                                                    <a style="display: inline-block;text-decoration: none;-webkit-text-size-adjust: none;text-align: center;background-color: #0B2639;color: #00CFFF" href="{{ frontend_url('password/recovery', $token) }}" target="_blank">
                                                                                                                                                                    <span style="font-size:12px;line-height:24px;"><span style="font-size: 14px; line-height: 28px;" data-mce-style="font-size: 14px;">RESET YOUR PASSWORD</span></span>
                                                                                                                                                                    </a>
                                                                                                                                                                    <!--[if !mso]><!- - -->
                                                                                                                                                                </td>
                                                                                                                                                            </tr>
                                                                                                                                                        </tbody>
                                                                                                                                                    </table>
                                                                                                                                                </div>
                                                                                                                                                <!--<![endif]-->
                                                                                                                                                <!--[if mso]>
                                                                                                                                            </center>
                                                                                                                                        </v:roundrect>
                                                                                                                                        <![endif]-->
                                                                                                                                    </td>
                                                                                                                                </tr>
                                                                                                                            </tbody>
                                                                                                                        </table>
                                                                                                                    </td>
                                                                                                                </tr>
                                                                                                            </tbody>
                                                                                                        </table>
                                                                                                    </td>
                                                                                                </tr>
                                                                                            </tbody>
                                                                                        </table>
                                                                                    </div>
                                                                                    <!--[if (gte mso 9)|(IE)]>
                                                                                </td>
                                                                                <![endif]--><!--[if (gte mso 9)|(IE)]></td>
                                                                            </tr>
                                                                        </table>
                                                                        <![endif]-->
                                                                    </td>
                                                                </tr>
                                                            </tbody>
                                                        </table>
                                                    </td>
                                                </tr>
                                            </tbody>
                                        </table>
                                        <!--[if mso]>
                                    </td>
                                </tr>
                            </table>
                            <![endif]-->
                            <!--[if (IE)]>
                        </td>
                    </tr>
                </table>
                <![endif]-->
            </td>
        </tr>
    </tbody>
</table>
<table style="border-spacing: 0;border-collapse: collapse;vertical-align: top;background-color: #0B2639" cellpadding="0" cellspacing="0" align="center" width="100%" border="0">
    <tbody>
        <tr style="vertical-align: top">
            <td style="word-break: break-word;border-collapse: collapse !important;vertical-align: top" width="100%">
                <!--[if gte mso 9]>
                <table id="outlookholder" border="0" cellspacing="0" cellpadding="0" align="center">
                    <tr>
                        <td>
                            <![endif]-->
                            <!--[if (IE)]>
                            <table width="500" align="center" cellpadding="0" cellspacing="0" border="0">
                                <tr>
                                    <td>
                                        <![endif]-->
                                        <table class="container" style="border-spacing: 0;border-collapse: collapse;vertical-align: top;max-width: 500px;margin: 0 auto;text-align: inherit" cellpadding="0" cellspacing="0" align="center" width="100%" border="0">
                                            <tbody>
                                                <tr style="vertical-align: top">
                                                    <td style="word-break: break-word;border-collapse: collapse !important;vertical-align: top" width="100%">
                                                        <table class="block-grid" style="border-spacing: 0;border-collapse: collapse;vertical-align: top;width: 100%;max-width: 500px;color: #000000;background-color: transparent" cellpadding="0" cellspacing="0" width="100%" bgcolor="transparent">
                                                            <tbody>
                                                                <tr style="vertical-align: top">
                                                                    <td style="word-break: break-word;border-collapse: collapse !important;vertical-align: top;text-align: center;font-size: 0">
                                                                        <!--[if (gte mso 9)|(IE)]>
                                                                        <table width="100%" align="center" bgcolor="transparent" cellpadding="0" cellspacing="0" border="0">
                                                                            <tr>
                                                                                <![endif]--><!--[if (gte mso 9)|(IE)]>
                                                                                <td valign="top" width="500">
                                                                                    <![endif]-->
                                                                                    <div class="col num12" style="display: inline-block;vertical-align: top;width: 100%">
                                                                                        <table style="border-spacing: 0;border-collapse: collapse;vertical-align: top" cellpadding="0" cellspacing="0" align="center" width="100%" border="0">
                                                                                            <tbody>
                                                                                                <tr style="vertical-align: top">
                                                                                                    <td style="word-break: break-word;border-collapse: collapse !important;vertical-align: top;background-color: transparent;padding-top: 5px;padding-right: 0px;padding-bottom: 5px;padding-left: 0px;border-top: 0px solid transparent;border-right: 0px solid transparent;border-bottom: 0px solid transparent;border-left: 0px solid transparent">
                                                                                                        <table style="border-spacing: 0;border-collapse: collapse;vertical-align: top" cellpadding="0" cellspacing="0" width="100%">
                                                                                                            <tbody>
                                                                                                                <tr style="vertical-align: top">
                                                                                                                    <td style="word-break: break-word;border-collapse: collapse !important;vertical-align: top;padding-top: 15px;padding-right: 15px;padding-bottom: 15px;padding-left: 15px">
                                                                                                                        <div style="color:#A4AEB5;line-height:150%;font-family:'Open Sans', 'Helvetica Neue', Helvetica, Arial, sans-serif;">
                                                                                                                            <div style='font-size:12px;line-height:18px;font-family:"Open Sans", "Helvetica Neue", Helvetica, Arial, sans-serif;color:#A4AEB5;text-align:left;'>
                                                                                                                                <p style="margin: 0;font-size: 14px;line-height: 21px;text-align: center"><span style="font-size: 11px; line-height: 16px;">This email was intended for {{ $user->full_name }}. <a style="color:#00CFFF;text-decoration: underline;" href="{{ frontend_url('unsubscribe') }}" target="_blank">Unsubscribe</a></span></p>
                                                                                                                                <p style="margin: 0;font-size: 11px;line-height: 16px;text-align: center"><span style="font-size: 11px; line-height: 16px;">You may change your settings <a style="color:#00CFFF;text-decoration: underline;" href="http://zoomtivity.com/settings" target="_blank">here</a>.</span></p>
                                                                                                                                <p style="margin: 0;font-size: 14px;line-height: 21px;text-align: center"><span style="font-size: 11px; line-height: 16px;">&#169; 2016 Zoomtivity.com</span></p>
                                                                                                                            </div>
                                                                                                                        </div>
                                                                                                                    </td>
                                                                                                                </tr>
                                                                                                            </tbody>
                                                                                                        </table>
                                                                                                    </td>
                                                                                                </tr>
                                                                                            </tbody>
                                                                                        </table>
                                                                                    </div>
                                                                                    <!--[if (gte mso 9)|(IE)]>
                                                                                </td>
                                                                                <![endif]--><!--[if (gte mso 9)|(IE)]></td>
                                                                            </tr>
                                                                        </table>
                                                                        <![endif]-->
                                                                    </td>
                                                                </tr>
                                                            </tbody>
                                                        </table>
                                                    </td>
                                                </tr>
                                            </tbody>
                                        </table>
                                        <!--[if mso]>
                                    </td>
                                </tr>
                            </table>
                            <![endif]-->
                            <!--[if (IE)]>
                        </td>
                    </tr>
                </table>
                <![endif]-->
            </td>
        </tr>
    </tbody>
</table>
@endsection