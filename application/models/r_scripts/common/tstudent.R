tstudent=function(zx, zy) {
	if(any(class(zx) == 'factor')) {
		group <- zx
		response <- zy
	} else {
		response <- zx
		group <- zy
	}
# test t.studenta dla dwóch grup niezależnych
	jednorodne(response, group) -> var.eq
	ttest <- t.test(response~group, var.equal = var.eq)
	
# wyliczenia mocy testu (tylko jeżeli wariancje są jednorodne i liczebności równe
	sd1 <- sd(response[group==levels(factor(group))[1]], na.rm=T)
	sd2 <- sd(response[group==levels(factor(group))[2]], na.rm=T)
	var1 <- var(response[group==levels(factor(group))[1]], na.rm=T)
	var2 <- var(response[group==levels(factor(group))[2]], na.rm=T)
	sdm = (sd1+sd2)/2
	liczbaobserwacji <- sum(table(factor(response),factor(group)))
	liczgrupA <- sum(table(factor(response),factor(group))[,1])
	liczgrupB <- sum(table(factor(response),factor(group))[,2])
	chisq.test(c(liczgrupA,liczgrupB)) -> chikw
	if (chikw$p.value<0.01) rownoliczne=FALSE else rownoliczne =TRUE
	if (liczgrupA<liczgrupB) {
		stosunekl <- abs(liczgrupB/liczgrupA)} else {
		stosunekl <- abs(liczgrupA/liczgrupB)
	} 
	if (var1<var2) {
		stosunekv <- abs(var2/var1)} else {
		stosunekv <- abs(var1/var2)
	} 
# jeżeli wariancje są jednorodne, to te wartości powinny być prawie identyczne w grupach, biorę pierwszą z brzega (kobiety)
	roznicasrednich <- abs(ttest$estimate[1]-ttest$estimate[2])
	dcohena <- roznicasrednich/sqrt(((liczgrupA-1)*sd1^2+(liczgrupB-1)*sd2^2)/liczbaobserwacji)
	unname(dcohena)
	round(as.numeric(dcohena), digits=2)
	if (rownoliczne==TRUE) {
		power.t.test(n = liczbaobserwacji/2, delta = roznicasrednich, sd = sdm, sig.level = 0.05, power = NULL) -> t.power
		wariant=1.1
		wyniki <- c(wariant, var.eq, rownoliczne, stosunekl, stosunekv, ttest$statistic, ttest$parameter, ttest$p.value, ttest$estimate[1],
				ttest$estimate[2], liczgrupA, liczgrupB, dcohena, t.power$n, t.power$delta, t.power$sd, t.power$sig.level, t.power$power)
		names(wyniki) <- c('Wariant analizy', 'Równość wariancji', 'Równoliczność grup', 'Stosunek liczbności grup jak 1:',
				'Stosunek wariancji jak 1:', 'Wartość testu t', 'Liczba stopni swobody', 'Istotność', 'Średnia w grupie 1', 'Średnia w grupie 2',
				'Liczebność grupa 1', 'Liczebność grupa 2', 'Statystyka d-Cohena', 'Liczebność grup (każdej)', 'Różnica średnich',
				'Odchylenie standardowe próbek', 'Założony poziom istotności', 'Moc testu')
		write.csv2(wyniki, file='tstudent.csv')
	} else {
		wariant=1.2
		wyniki <- c(wariant, var.eq, rownoliczne, stosunekl, stosunekv, ttest$statistic, ttest$parameter, ttest$p.value, ttest$estimate[1],
				ttest$estimate[2], liczgrupA, liczgrupB, dcohena)
		names(wyniki) <- c('Wariant analigroup', 'Równość wariancji', 'Równoliczność grup', 'Stosunek liczbności grup jak 1:',
				'Stosunek wariancji jak 1:', 'Wartość testu t', 'Liczba stopni swobody', 'Istotność', 'Średnia w grupie 1',
				'Średnia w grupie 2', 'Liczebność grupa 1', 'Liczebność grupa 2', 'Statystyka d-Cohena')
		write.csv2(wyniki, file='tstudent.csv')
	}
}
