analiza_srednich=function(zx, zy) {
	if(any(class(zx) == 'factor')) {
		group <- zx
		response <- zy
	} else {
		response <- zx
		group <- zy
	}

	param <- c()
	group <- as.factor(group)
	for (i in 1:length(levels(factor(group)))) {
		param[i] <- parametrycznosc(response[group==levels(factor(group))[i]])
	}
	if (all(param)==TRUE) {
		# robimy ścieżkę parametryczną
		if (length(levels(factor(group)))==2) {
			tstudent(response, group)
		} else if (length(levels(factor(group)))>2) {
			an.var(response, group)
		} else {
			message('Error: Druga zmienna (group) nie jest czynnikiem lub nie jest zmienną (ma jeden poziom)')
			q(save="no")
		}
	} else {
		# robimy ścieżkę nieparametryczną
		if (length(levels(factor(group)))==2) {
			wilcoxon(response, group)
		} else if (length(levels(factor(group)))>2) {
			kraskal(response, group)
		} else {
			message('Error: Druga zmienna (group) nie jest czynnikiem lub nie jest zmienną (ma jeden poziom)')
			q(save="no")
		}
	}
	analiza_srednich=licz.srednie(response, group)
}
