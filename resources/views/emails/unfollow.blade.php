@extends('layouts.emails.main')
@section('content')
<table style="border-spacing: 0;border-collapse: collapse;vertical-align: top;background-color: transparent" cellpadding="0" cellspacing="0" align="center" width="100%" border="0">
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
                                                        <table class="block-grid mixed-two-up" style="border-spacing: 0;border-collapse: collapse;vertical-align: top;width: 100%;max-width: 500px;color: #333;background-color: transparent" cellpadding="0" cellspacing="0" width="100%" bgcolor="transparent">
                                                            <tbody>
                                                                <tr style="vertical-align: top">
                                                                    <td style="word-break: break-word;border-collapse: collapse !important;vertical-align: top;text-align: center;font-size: 0">
                                                                        <!--[if (gte mso 9)|(IE)]>
                                                                        <table width="100%" align="center" bgcolor="transparent" cellpadding="0" cellspacing="0" border="0">
                                                                            <tr>
                                                                                <![endif]--><!--[if (gte mso 9)|(IE)]>
                                                                                <td valign="top" width="167">
                                                                                    <![endif]-->
                                                                                    <div class="col num4" style="display: inline-block;vertical-align: top;text-align: center;width: 167px">
                                                                                        <table style="border-spacing: 0;border-collapse: collapse;vertical-align: top" cellpadding="0" cellspacing="0" align="center" width="100%" border="0">
                                                                                            <tbody>
                                                                                                <tr style="vertical-align: top">
                                                                                                    <td style="word-break: break-word;border-collapse: collapse !important;vertical-align: top;background-color: transparent;padding-top: 15px;padding-right: 10px;padding-bottom: 15px;padding-left: 10px;border-top: 0px solid transparent;border-right: 0px solid transparent;border-bottom: 0px solid transparent;border-left: 0px solid transparent">
                                                                                                        <table style="border-spacing: 0;border-collapse: collapse;vertical-align: top" cellpadding="0" cellspacing="0" width="100%" border="0">
                                                                                                            <tbody>
                                                                                                                <tr style="vertical-align: top">
                                                                                                                    <td style="word-break: break-word;border-collapse: collapse !important;vertical-align: top;width: 100%;padding-top: 0px;padding-right: 0px;padding-bottom: 0px;padding-left: 0px" align="center">
                                                                                                                        <div style="font-size:12px" align="center">
                                                                                                                            <a href="{{ frontend_url($sender->id) }}" target="_blank">
                                                                                                                            <img class="center" style="outline: none;text-decoration: none;-ms-interpolation-mode: bicubic;clear: both;display: block;border: none;height: auto;line-height: 100%;margin: 0 auto;float: none;width: 100px;max-width: 100px" align="center" border="0" src="{{ $sender->avatar->url('thumb') }}" alt="{{ $sender->first_name }} {{ $sender->last_name }}" title="{{ $sender->first_name }} {{ $sender->last_name }}" width="100">
                                                                                                                            </a>
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
                                                                                <![endif]--><!--[if (gte mso 9)|(IE)]>
                                                                                <td valign="top" width="333">
                                                                                    <![endif]-->
                                                                                    <div class="col num8" style="display: inline-block;vertical-align: top;text-align: center;width: 333px">
                                                                                        <table style="border-spacing: 0;border-collapse: collapse;vertical-align: top" cellpadding="0" cellspacing="0" align="center" width="100%" border="0">
                                                                                            <tbody>
                                                                                                <tr style="vertical-align: top">
                                                                                                    <td style="word-break: break-word;border-collapse: collapse !important;vertical-align: top;background-color: transparent;padding-top: 15px;padding-right: 0px;padding-bottom: 15px;padding-left: 0px;border-top: 0px solid transparent;border-right: 0px solid transparent;border-bottom: 0px solid transparent;border-left: 0px solid transparent">
                                                                                                        <table style="border-spacing: 0;border-collapse: collapse;vertical-align: top" cellpadding="0" cellspacing="0" width="100%">
                                                                                                            <tbody>
                                                                                                                <tr style="vertical-align: top">
                                                                                                                    <td style="word-break: break-word;border-collapse: collapse !important;vertical-align: top;padding-top: 10px;padding-right: 10px;padding-bottom: 10px;padding-left: 10px">
                                                                                                                        <div style="color:#000;line-height:120%;font-family:'Open Sans', 'Helvetica Neue', Helvetica, Arial, sans-serif;">
                                                                                                                            <div style="font-size:12px;line-height:14px;color:#000;font-family:'Open Sans', 'Helvetica Neue', Helvetica, Arial, sans-serif;text-align:left;">
                                                                                                                                <p style="margin: 0;font-size: 12px;line-height: 14px;text-align: center"><span style="font-size: 16px; line-height: 19px;">Hi, {{ $user->full_name }}</span></p>
                                                                                                                            </div>
                                                                                                                        </div>
                                                                                                                    </td>
                                                                                                                </tr>
                                                                                                            </tbody>
                                                                                                        </table>
                                                                                                        <table style="border-spacing: 0;border-collapse: collapse;vertical-align: top" cellpadding="0" cellspacing="0" width="100%">
                                                                                                            <tbody>
                                                                                                                <tr style="vertical-align: top">
                                                                                                                    <td style="word-break: break-word;border-collapse: collapse !important;vertical-align: top;padding-top: 10px;padding-right: 10px;padding-bottom: 10px;padding-left: 10px">
                                                                                                                        <div style="color:#555555;line-height:120%;font-family:'Open Sans', 'Helvetica Neue', Helvetica, Arial, sans-serif;">
                                                                                                                            <div style='font-size:12px;line-height:14px;font-family:"Open Sans", "Helvetica Neue", Helvetica, Arial, sans-serif;color:#555555;text-align:left;'>
                                                                                                                                <p style="margin: 0;font-size: 12px;line-height: 14px;text-align: center"><span style="color: rgb(0, 0, 0); font-size: 14px; line-height: 16px;"><span style="line-height: 16px; font-size: 14px;">You are no longer following {{ $sender->first_name }} {{ $sender->last_name }}. Why can't we all just get along?</span>&nbsp;</span></p>
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
                                                                                                                                        <v:roundrect xmlns:v="urn:schemas-microsoft-com:vml" xmlns:w="urn:schemas-microsoft-com:office:word" href="http://zoomtivity.com"
                                                                                                                                            style="
                                                                                                                                            height:34px;
                                                                                                                                            v-text-anchor:middle;
                                                                                                                                            width:73px;"
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
                                                                                                                                                    <table style="border-spacing: 0;border-collapse: collapse;vertical-align: top;height: 34" width="100%" border="0" cellspacing="0" cellpadding="0">
                                                                                                                                                        <tbody>
                                                                                                                                                            <tr style="vertical-align: top">
                                                                                                                                                                <td style="word-break: break-word;border-collapse: collapse !important;vertical-align: top;border-radius: 0px;                   -webkit-border-radius: 0px;                   -moz-border-radius: 0px;                  color: #00CFFF;                  background-color: #0B2639;                  padding-top: 5px;                   padding-right: 20px;                  padding-bottom: 5px;                  padding-left: 20px;                  font-family: 'Open Sans', 'Helvetica Neue', Helvetica, Arial, sans-serif;text-align: center" valign="middle">
                                                                                                                                                                    <!--<![endif]-->
                                                                                                                                                                    <a style="display: inline-block;text-decoration: none;-webkit-text-size-adjust: none;text-align: center;background-color: #0B2639;color: #00CFFF" href="http://zoomtivity.com" target="_blank">
                                                                                                                                                                    <span style="font-size:12px;line-height:24px;"><strong>LOGIN</strong></span>
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