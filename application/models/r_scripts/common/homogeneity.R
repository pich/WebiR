jednorodne=function(zx, zy,writeResult=FALSE) {
	# Testowanie jednorodności wariancji (do 'anovy')
	if(any(class(zx) == 'factor')) {
		group <- zx
		response <- zy
	} else {
		response <- zx
		group <- zy
	}
	jednorodnosc <- bartlett.test(response, group)
	okragla <- round(jednorodnosc$p.value, digits=4)
	wyniki <- c(jednorodnosc$statistic, jednorodnosc$parameter, jednorodnosc$p.value, okragla)
	names(wyniki) <- c('K-kwadrat Bartletta', 'df', 'p dokładne', 'p zaokrąglone')
	if (jednorodnosc$p.value<0.05) is_homo=FALSE else is_homo=TRUE
	if(writeResult == TRUE) {
		write.csv2(is_homo,file="homogeneity.csv")
	}
	jednorodne = is_homo
}
