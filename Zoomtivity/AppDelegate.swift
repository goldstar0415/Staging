//
//  AppDelegate.swift
//  Zoomtivity
//
//  Created by Callum Trounce on 17/02/2017.
//  Copyright © 2017 Zoomtivity. All rights reserved.
//

import UIKit
import CoreData
import SKMaps

@UIApplicationMain
class AppDelegate: UIResponder, UIApplicationDelegate {

    var window: UIWindow?


    func application(_ application: UIApplication, didFinishLaunchingWithOptions launchOptions: [UIApplicationLaunchOptionsKey: Any]?) -> Bool {
        // Override point for customization after application launch.
        
        let initSettings = SKMapsInitSettings()
        initSettings.mapDetailLevel = .full
        
        SKMapsService.sharedInstance().initializeSKMaps(withAPIKey: "9c229a702b7b69ec8b34af4341d2196a307576c81fb5606e45e831f5feda5fa3",
                                                        settings: initSettings)
        return true
    }

   

    func applicationWillTerminate(_ application: UIApplication) {
        // Called when the application is about to terminate. Save data if appropriate. See also applicationDidEnterBackground:.
        // Saves changes in the application's managed object context before the application terminates.
        DatabaseManager.sharedDataManager.save()
    }

    // MARK: - Core Data stack

    

}

