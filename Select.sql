SELECT
	"p"."idPreProjeto" AS "idProjeto",
	"p"."NomeProjeto" AS "NomeProposta",
	"p"."idAgente",
	CONVERT(
		CHAR(20),
		m.DtMovimentacao,
		120
	) AS DtMovimentacao,
	DATEDIFF(
		d,
		m.DtMovimentacao,
		GETDATE()
	) AS diasDesdeMovimentacao,
	"m"."idMovimentacao",
	"m"."Movimentacao" AS "CodSituacao",
	CONVERT(
		CHAR(20),
		x.DtAvaliacao,
		120
	) AS DtAdmissibilidade,
	DATEDIFF(
		d,
		x.DtAvaliacao,
		GETDATE()
	) AS diasCorridos,
	"x"."idTecnico" AS "idUsuario",
	"x"."DtAvaliacao",
	"x"."idAvaliacaoProposta",
	(
		SELECT
			"Usuarios"."usu_nome"
		FROM
			"tabelas"."dbo"."Usuarios"
		WHERE
			(
				usu_codigo = x.idTecnico
			)
	) AS "Tecnico",
	(
		SELECT
			"vwUsuariosOrgaosGrupos"."org_superior"
		FROM
			"tabelas"."dbo"."vwUsuariosOrgaosGrupos"
		WHERE
			(
				usu_codigo = x.idTecnico
			)
			AND(
				sis_codigo = 21
			)
			AND(
				gru_codigo = 92
			)
		GROUP BY
			"org_superior"
	) AS "idSecretaria",
	"a"."CNPJCPF",
	DATEDIFF(
		d,
		ap1.DtEnvio,
		GETDATE()
	) AS diasDiligencia,
	DATEDIFF(
		d,
		ap2.dtResposta,
		GETDATE()
	) AS diasRespostaDiligencia
FROM
	"SAC"."dbo"."preprojeto" AS "p"
INNER JOIN "SAC"."dbo"."tbMovimentacao" AS "m" ON
	p.idPreProjeto = m.idProjeto
	AND m.stEstado = 0
INNER JOIN "SAC"."dbo"."tbAvaliacaoProposta" AS "x" ON
	p.idPreProjeto = x.idProjeto
	AND x.stEstado = 0
INNER JOIN "agentes"."dbo"."Agentes" AS "a" ON
	p.idAgente = a.idAgente
INNER JOIN "sac"."dbo"."Verificacao" AS "y" ON
	m.Movimentacao = y.idVerificacao
LEFT JOIN "sac"."dbo"."tbAvaliacaoProposta" AS "ap1" ON
	p.idPreProjeto = ap1.idProjeto
	AND ap1.stEnviado = 'S'
LEFT JOIN "sac"."dbo"."tbAvaliacaoProposta" AS "ap2" ON
	p.idPreProjeto = ap2.idProjeto
	AND ap2.stEnviado = 'S'
WHERE
	(
		m.Movimentacao IN(
			96,
			97,
			128
		)
	)
	AND(
		NOT EXISTS(
			SELECT
				TOP(1) IdPRONAC
			FROM
				SAC.dbo.Projetos AS u
			WHERE
				(
					p.idPreProjeto = idProjeto
				)
		)
	)
	AND(
		x.idTecnico = 236
	)
ORDER BY
	"x"."DtAvaliacao" DESC
;	

	
	
SELECT movimentacao from sac.dbo.tbMovimentacao group by Movimentacao

SELECT * from sac.dbo.verificacao where idVerificacao in (96,97,128)