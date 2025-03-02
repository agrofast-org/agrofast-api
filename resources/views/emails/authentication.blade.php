<table
    style="font-family: Trebuchet MS, Arial, sans-serif; max-width: 600px; width: 100%; border: 1px solid #F9F9F9; border-collapse: collapse;"
    cellspacing="0" cellpadding="0" border="0" align="center">
    <tbody>
        <!-- <tr style="background-color: #fff;">
            <td style="padding: 15px;" width="120" valign="middle">
                <img src="https://i.imgur.com/a.png" alt="NomeDaAplicação Logo" style="max-width: 100%;">
            </td>
        </tr> -->

        <tr>
            <td colspan="2" style="padding: 20px; color: #333333; background-color: #F9F9F9;">
                <h2 style="margin-top: 0; color: #1D3055;">Seja bem-vindo(a) à NomeDaAplicação, {{$user['name']}},</h2>
                <!-- <p style="margin: 10px 0; line-height: 1.6;">
                    Temos o prazer de informar que sua conta foi criada com sucesso. Para acessar a plataforma, clique no
                    botão abaixo.
                </p> -->
                <p style="margin: 10px 0; line-height: 1.6;">Use o código abaixo para verificar sua sessão:</p>
                <div style="text-align: center; margin: 20px 0; font-size: 24px; font-weight: bold;">
                    {{$info['code']}}
                </div>
                <p style="margin: 10px 0; line-height: 1.6; font-size: 14px; color: #666666;">
                    Este link expira em {{$info['expires']}}.
                </p>
            </td>
        </tr>

        <!-- <tr>
            <td colspan="2"
                style="padding: 16px; color: #666666; font-size: 14px; line-height: 1.6; background-color: #F3F3F3; text-align: center;">
                Dúvidas? Acesse nossa
                <a href="https://ababa.com/central-de-relacionamento" style="color: #1D3055; text-decoration: underline;"
                    target="_blank">
                    Central de Relacionamento
                </a>.
            </td>
        </tr> -->
    </tbody>
</table>
