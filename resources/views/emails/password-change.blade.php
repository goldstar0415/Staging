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
                                                                                                                                <p style="margin: 0;font-size: 14px;line-height: 17px;text-align: center"><span style="line-height: 16px; font-size: 14px;">Hi {{ $user->full_name }},</span></p>
                                                                                                                                <p style="margin: 0;font-size: 14px;line-height: 16px;text-align: center"><span style="font-size: 14px; line-height: 16px;"><span style="line-height: 16px; font-size: 14px;">&nbsp;</span>&nbsp;</span><br></p>
                                                                                                                                <span style="line-height: 16px; font-size: 14px;">This is to notify you that your password has been changed.</span>
                                                                                                                            </div>
                                                                                                                            <div style='font-size:12px;line-height:14px;text-align:center;font-family:Arial, "Helvetica Neue", Helvetica, sans-serif;color:#555555;'>
                                                                                                                                <span style="line-height:17px; font-size:14px;">&nbsp;</span><br><span style="font-size: 12px; line-height: 14px;">If you did not make this change, using this email address please contact support at <a style="color:#0000FF" href="mailto:security@zoomtivity.com">security@zoomtivity.com</a> immediately as your account may have been accessed by someone else.&nbsp;</span><br>
                                                                                                                                <p style="margin: 0;font-size: 14px;line-height: 16px;text-align: center">&nbsp;<br></p>
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