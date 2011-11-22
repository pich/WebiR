poziomy	= function(x) {
	poz=data.frame()
	for (i in 1:length(x)) {
		if(any(class(x[,i]) == 'factor')) {
			wsio <- (levels(x[,i]))
			for (k in 1:length(wsio)) {wsio[k] -> poz[i,k]}
		} else {
			poz[i,1] <- c('')
		}
	}
	poz
	row.names(poz) <- names(x)
	write.csv2(poz, file='levels.csv')
}

poziomy.factor = function(x) {
	poz = levels(x)
	write.csv2(poz,'file.levels.csv')
}

poziomy.numeric = function(x) {
	poz = unique(x)
	write.csv2(poz,'file.levels.csv')
}