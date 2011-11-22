chi2=function(zx, zy) # wystarczy podać dwie zmienne typu factor
{
	library(pwr)
	library(vcd)
	
	### test chi2
	chisq.test(factor(zx), factor(zy)) -> chikwadrat
#	chikwadrat
	
	xtabs(~factor(zx)+ factor(zy))-> tabela # tabela robocza na potrzeby wyliczenia statystyk, factor() opuszcza niewystępujące wartości
	dim(tabela) -> wymiar
	sum(tabela) -> liczba_obiektow
	ES.w2(tabela) -> w_cohena
	if(is.nan(w_cohena)) {
		powr <- list(NA,NA,NA,NA,NA)
		names(powr) <- c("w","N","df","sig.level","power")
	} else if (is.infinite(w_cohena)) {
			powr <- list(NA,NA,NA,NA,NA)
			names(powr) <- c("w","N","df","sig.level","power")
	} else {
		pwr.chisq.test(w=w_cohena,df=(wymiar[1]-1)*(wymiar[2]-1),N=liczba_obiektow,sig.level = 0.05) -> powr	
	}
	
	### statystyki siły związku
	assocstats(tabela) -> sila
	###	eksport danych do csv
	stats <-c(chikwadrat$statistic, chikwadrat$parameter, chikwadrat$p.value, powr$w, powr$N, powr$df, powr$sig.level,
			powr$power, sila$phi, sila$cramer)
	unname(stats)
	names(stats) <-c("wartość chi^2", "df", "istotność", "w", "N", "df-w", "istotność-w",
			"moc testu", "phi", "V-Cramera")
	tab_p_w=tabela
	tab_p_k=tabela
	tab_p=tabela
	for (k in 1:wymiar[2]) {
		suma_k <- sum(tabela[,k])
		for (w in 1:wymiar[1]) {
			suma_w <- sum(tabela[w,])
			tab_p_w[w,k] <- (tabela[w,k]/suma_w)*100
			tab_p_k[w,k] <- (tabela[w,k]/suma_k)*100
			tab_p[w,k]   <- ((tabela[w,k]/liczba_obiektow)*100)
		}
	}
	write.csv2(tab_p_w, file='observed_pct_r.csv')
	write.csv2(tab_p_k, file='observed_pct_c.csv')
	write.csv2(tab_p, file='observed_pct.csv')
	write.csv2(stats, file='statistics.csv')
	write.csv2(chikwadrat$observed, file='observed.csv')
	write.csv2(chikwadrat$expected, file='expected.csv')
	write.csv2(chikwadrat$residuals, file='residuals.csv')
}
