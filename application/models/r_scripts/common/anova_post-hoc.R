# ANOVA i post-hoc
an.var=function(zx, zy,compare=TRUE)	{
	if(any(class(zx) == 'factor')) {
		group <- zx
		response <- zy
	} else {
		response <- zx
		group <- zy
	}
	jednorodne(response, group) -> var.eq
	
	# Sprawdzenie, cgroup grupy są równoliczne (balanced design)
	sumywkat <- margin.table(table(response,group), 2)
	liczbazbadanych <- sum(table(response,group))
	sumywkat <- as.matrix(sumywkat)
	chikw.r <- chisq.test(sumywkat, correct = TRUE, p = rep(1/length(sumywkat), length(sumywkat))) #jeżeli p<0,05 to znacgroup nierówne grupy
	if (chikw.r$p.value>=0.05) {rownoliczne=TRUE} else {rownoliczne=FALSE}
	if (var.eq==TRUE) {
# Założono równość wariancji
		tanova <- anova(lm(response~group)) # wsgroupstkie spełnione
		if (rownoliczne == TRUE) {
			# wyliczenie mocy ANOVY, wsgroupstkie założenia OK	
			library(pwr)
			wariant=3.1
			power <- pwr.anova.test(k = length(sumywkat), n = liczbazbadanych/length(sumywkat), f = tanova[1,4], sig.level = 0.05, power = NULL)
			wyniki=c(wariant, tanova$Df[1], tanova$Df[2], tanova$F[1], tanova$Pr[1], var.eq,rownoliczne, power$k, power$n, power$sig.level, power$power)
			names(wyniki)=c('Wariant analizy', 'Stopnie swobody 1', 'Stopnie swobody 2', 'F', 'istotność','jednorodne','równoliczne', 'liczba grup', 'liczebność grup', 'założony poziom istotności', 'moc (x100)')
			write.csv2(wyniki, file='anova.csv')
		} else {
			wariant=3.2
			wyniki=c(wariant, tanova$Df[1], tanova$Df[2], tanova$F[1], tanova$Pr[1],var.eq,rownoliczne)
			names(wyniki) <- c('Wariant analizy', 'df1', 'df2', 'F', 'istotność','jednorodne','rownoliczne')
			write.csv2(wyniki, file='anova.csv')
		}
# Siła efektu: eta kwadrat
		etakwadrat <- tanova[1,2]/(tanova[1,2]+tanova[2,2])
		names(etakwadrat) ='Eta kwadrat'
		write.csv2(round(etakwadrat, digits=3), file='s_eff.csv')
	} else {
# Brak założenia o równości wariancji
		wariant=3.3
		names(wariant)='Wariant analigroup'
		tanova <- oneway.test(response~group)
		wyniki <- c(wariant, tanova$statistic, tanova$parameter[1], tanova$parameter[2], tanova$p.value, var.eq,rownoliczne)
		names(wyniki) <- c("Wariant analizy","F","num df","denom df","istotność","jednorodne","równoliczne");
		write.csv2(wyniki, file='anova.csv')
	}
	
	if(compare == TRUE) {
	# Porównania grup
		require(graphics)
		aovanova <- aov(response~group)
		tukej <- TukeyHSD(aovanova) 
		write.csv2(tukej$group, file='compare.csv')
	}
}
